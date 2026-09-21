<?php

namespace App\Services\Berita;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\Core\Exceptions\AnthropicException;
use App\Contracts\PenulisBerita;
use Throwable;

/**
 * Menyusun berita dari bahan 5W+1H memakai model Claude.
 *
 * Yang diminta ke model bukan "carikan berita", melainkan "susun fakta ini
 * menjadi berita". Bedanya besar untuk tanggung jawab redaksi: seluruh fakta
 * datang dari penyusunnya, model hanya mengurus bentuk kalimat. Batas itu
 * ditegakkan di prompt sistem dan diuji di PenulisClaudeTest.
 */
class PenulisClaude implements PenulisBerita
{
    public function tulis(BahanBerita $bahan): NaskahBerita
    {
        $kunci = (string) config('anthropic.api_key');

        if (trim($kunci) === '') {
            throw new GagalMenulis(
                'Kunci API Anthropic belum diisi. Tambahkan ANTHROPIC_API_KEY di berkas .env, lalu muat ulang halaman ini.'
            );
        }

        $klien = new Client(apiKey: $kunci);

        try {
            $pesan = $klien->beta->messages->create(
                model: (string) config('anthropic.model'),
                maxTokens: (int) config('anthropic.max_tokens'),
                system: $this->prompt(),
                messages: [['role' => 'user', 'content' => $this->permintaan($bahan)]],
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

    private function skema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'judul' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 3,
                    'maxItems' => 3,
                    'description' => 'Tiga judul alternatif dengan sudut berbeda, masing-masing maksimal 80 karakter.',
                ],
                'lead' => [
                    'type' => 'string',
                    'description' => 'Paragraf pembuka, 25 sampai 40 kata, menjawab pertanyaan terpenting.',
                ],
                'isi' => [
                    'type' => 'string',
                    'description' => 'Badan berita tanpa lead. Antarparagraf dipisah satu baris kosong. Tanpa markup.',
                ],
                'meta' => [
                    'type' => 'string',
                    'description' => 'Ringkasan untuk meta description, maksimal 155 karakter.',
                ],
                'slug' => [
                    'type' => 'string',
                    'description' => 'Slug URL dari judul pertama: huruf kecil, antar kata dipisah tanda hubung.',
                ],
            ],
            'required' => ['judul', 'lead', 'isi', 'meta', 'slug'],
            'additionalProperties' => false,
        ];
    }

    private function prompt(): string
    {
        return <<<'TEKS'
        Kamu penulis berita untuk kanal resmi pemerintahan desa di Indonesia.

        Tugasmu menyusun fakta yang diberikan menjadi berita berbahasa Indonesia yang rapi.
        Kamu tidak meliput dan tidak mencari data. Kamu tidak mengetahui apa pun tentang
        peristiwa ini selain yang tertulis pada bahan.

        Aturan yang tidak boleh dilanggar:

        1. Pakai hanya fakta yang ada di bahan. Jangan menambah nama orang, jabatan, angka,
           tanggal, tempat, lembaga, atau jumlah peserta yang tidak disebutkan.
        2. Kalau sebuah keterangan tidak ada di bahan, tulis beritanya tanpa keterangan itu.
           Jangan menduga, jangan menulis "diperkirakan" atau "sekitar", dan jangan menambal
           dengan kalimat umum yang terdengar meyakinkan.
        3. Kutipan narasumber disalin persis, huruf demi huruf. Kamu hanya boleh menambahkan
           kalimat pengantar dan penutup kutipan seperti "ujarnya" atau "katanya". Dilarang
           memperhalus, memenggal, atau menyusun ulang kata-kata di dalam tanda kutip.
        4. Jangan mengarang kutipan baru. Kalau tidak ada kutipan di bahan, tulis berita
           tanpa kutipan sama sekali.
        5. Jangan menambahkan pujian, harapan, ajakan, atau imbauan yang tidak ada di bahan.

        Bentuk tulisan:

        - Piramida terbalik: keterangan terpenting di depan.
        - Lead satu paragraf yang menjawab pertanyaan paling penting.
        - Badan berita menguraikan sisanya, satu gagasan untuk satu paragraf.
        - Kalimat aktif, hemat kata, tanpa istilah asing yang ada padanannya.
        - Tanggal ditulis lengkap dengan gaya Indonesia, misalnya 21 September 2026.

        Judul:

        - Tiga alternatif dengan sudut yang benar-benar berbeda, bukan variasi susunan kata.
        - Maksimal 80 karakter, tanpa tanda seru, dan tidak seluruhnya huruf kapital.
        TEKS;
    }

    private function permintaan(BahanBerita $bahan): string
    {
        $bagian = ["BAHAN BERITA\n\n".$bahan->sebagaiDaftar()];

        if ($bahan->punyaKutipan()) {
            $penutur = trim($bahan->penuturKutipan) !== ''
                ? trim($bahan->penuturKutipan)
                : 'narasumber pada bahan di atas';

            $bagian[] = "KUTIPAN NARASUMBER\n\n"
                ."Penutur: {$penutur}\n"
                ."Kutipan, salin persis apa adanya:\n"
                .trim($bahan->kutipan);
        }

        $bagian[] = "GAYA DAN PANJANG\n\n"
            .$bahan->nada->arahan()."\n"
            .$bahan->panjang->arahan();

        return implode("\n\n", $bagian);
    }
}
