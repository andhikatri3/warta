<?php

namespace App\Services\Berita;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Contracts\PenulisBerita;
use Throwable;

/**
 * Menyusun berita dari bahan 5W+1H memakai model Claude.
 *
 * Yang diminta ke model bukan "carikan berita", melainkan "susun fakta ini
 * menjadi berita". Bedanya besar untuk tanggung jawab redaksi: seluruh fakta
 * datang dari penyusunnya, model hanya mengurus bentuk kalimat. Batas itu
 * ditegakkan di PromptBerita, yang dipakai bersama semua penulis.
 */
class PenulisClaude implements PenulisBerita
{
    public function tulis(BahanBerita $bahan): NaskahBerita
    {
        $kunci = (string) config('anthropic.api_key');

        if (trim($kunci) === '') {
            throw new GagalMenulis(
                'Kunci API Anthropic belum diisi. Ambil kuncinya di console.anthropic.com '
                .'(menu API Keys), isikan ke ANTHROPIC_API_KEY di berkas .env, lalu muat ulang halaman ini.'
            );
        }

        $klien = new Client(apiKey: $kunci);

        try {
            $pesan = $klien->beta->messages->create(
                model: (string) config('anthropic.model'),
                maxTokens: (int) config('anthropic.max_tokens'),
                system: PromptBerita::sistem(),
                messages: [['role' => 'user', 'content' => PromptBerita::permintaan($bahan)]],
                outputConfig: [
                    'effort' => (string) config('anthropic.effort'),
                    'format' => ['type' => 'json_schema', 'schema' => $this->skema()],
                ],

                // Kalau model menolak permintaan karena alasan kebijakan,
                // layanan mencoba ulang pada model pengganti dalam panggilan
                // yang sama. Tanpa ini penolakan berhenti sebagai kegagalan.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],

                requestOptions: ['timeout' => (float) config('anthropic.timeout')],
            );
        } catch (AuthenticationException) {
            throw new GagalMenulis('Kunci API Anthropic ditolak. Periksa lagi nilainya di berkas .env.');
        } catch (RateLimitException) {
            throw new GagalMenulis('Permintaan sedang dibatasi. Tunggu sebentar, lalu coba lagi.');
        } catch (APITimeoutException) {
            throw new GagalMenulis('Model terlalu lama menjawab. Coba lagi, atau pilih panjang berita yang lebih pendek.');
        } catch (APIConnectionException) {
            throw new GagalMenulis('Tidak bisa menghubungi layanan Anthropic. Periksa sambungan internet server.');
        } catch (AnthropicException $e) {
            throw new GagalMenulis('Layanan menolak permintaan: '.$e->getMessage());
        } catch (Throwable $e) {
            throw new GagalMenulis('Gagal menulis berita: '.$e->getMessage());
        }

        // Diperiksa sebelum isinya dibaca: pada penolakan, content boleh jadi
        // kosong atau berisi keterangan, bukan naskah.
        if (($pesan->stopReason?->value ?? $pesan->stopReason) === 'refusal') {
            throw new GagalMenulis(
                'Model menolak menulis dari bahan ini. Periksa apakah ada isi yang sensitif, lalu susun ulang bahannya.'
            );
        }

        return NaskahBerita::dariLarik($this->uraikan($pesan));
    }

    private function uraikan(object $pesan): array
    {
        $teks = '';

        foreach ($pesan->content as $blok) {
            if (($blok->type ?? null) === 'text') {
                $teks .= $blok->text;
            }
        }

        $data = json_decode(trim($teks), true);

        if (! is_array($data)) {
            throw new GagalMenulis('Jawaban model tidak berbentuk naskah yang bisa dibaca. Coba jalankan sekali lagi.');
        }

        return $data;
    }

    /**
     * Skema bersama, diperketat untuk Anthropic.
     *
     * additionalProperties dan batas jumlah judul hanya ditambahkan di sini
     * karena tidak semua penyedia menerima kata kunci itu — yang tidak
     * mengenalinya menolak permintaan dengan galat 400, bukan mengabaikannya.
     *
     * @return array<string, mixed>
     */
    private function skema(): array
    {
        $skema = PromptBerita::skema();

        $skema['additionalProperties'] = false;
        $skema['properties']['judul']['minItems'] = 3;
        $skema['properties']['judul']['maxItems'] = 3;

        return $skema;
    }
}
