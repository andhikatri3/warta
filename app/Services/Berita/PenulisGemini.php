<?php

namespace App\Services\Berita;

use App\Contracts\PenulisBerita;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Menyusun berita memakai Gemini lewat Interactions API.
 *
 * Dipakai lewat HTTP biasa, bukan SDK. Dua alasan: bentuk permintaannya
 * sederhana dan sudah dipatok dengan header Api-Revision, dan klien HTTP
 * Laravel bisa dipalsukan di pengujian — jadi bentuk permintaan dan pembacaan
 * balasannya benar-benar teruji tanpa memanggil layanan sungguhan.
 *
 * Perhatikan bentuk balasannya. Pustaka resmi menyediakan `output_text`, tetapi
 * itu properti bantu di sisi pustaka, bukan ruas di kawat. Yang benar-benar
 * dikirim adalah larik `outputs`, dan teksnya bisa terpecah ke beberapa blok
 * yang harus disambung sendiri.
 */
class PenulisGemini implements PenulisBerita
{
    public function tulis(BahanBerita $bahan): NaskahBerita
    {
        $kunci = trim((string) config('gemini.api_key'));

        if ($kunci === '') {
            throw new GagalMenulis(
                'Kunci API Gemini belum diisi. Ambil kuncinya di aistudio.google.com '
                .'(menu Get API key), isikan ke GEMINI_API_KEY di berkas .env, lalu muat ulang halaman ini.'
            );
        }

        try {
            $balasan = Http::withHeaders([
                'x-goog-api-key' => $kunci,

                // Memaku revisi API ke yang dipakai saat kode ini ditulis.
                // Interactions API pernah berubah bentuk, dan tanpa pakuan ini
                // perubahan berikutnya akan merusak aplikasi tanpa ada yang
                // menyentuh kodenya.
                'Api-Revision' => (string) config('gemini.revisi'),
            ])
                ->timeout((int) config('gemini.timeout'))
                ->post(rtrim((string) config('gemini.base_url'), '/').'/interactions', [
                    'model' => (string) config('gemini.model'),
                    'system_instruction' => PromptBerita::sistem(),
                    'input' => PromptBerita::permintaan($bahan),
                    'response_format' => [
                        'type' => 'text',
                        'mime_type' => 'application/json',
                        'schema' => PromptBerita::skema(),
                    ],
                ]);
        } catch (ConnectionException) {
            throw new GagalMenulis(
                'Tidak bisa menghubungi layanan Gemini, atau modelnya terlalu lama menjawab. '
                .'Periksa sambungan internet server, lalu coba lagi.'
            );
        } catch (Throwable $e) {
            throw new GagalMenulis('Gagal menulis berita: '.$e->getMessage());
        }

        if ($balasan->failed()) {
            throw new GagalMenulis($this->pesanGagal($balasan));
        }

        return NaskahBerita::dariLarik($this->uraikan($balasan));
    }

    private function pesanGagal(Response $balasan): string
    {
        $rinci = trim((string) data_get($balasan->json(), 'error.message', ''));

        return match ($balasan->status()) {
            400 => 'Permintaan ditolak Gemini'.($rinci !== '' ? ': '.$rinci : '.'),
            401, 403 => 'Kunci API Gemini ditolak. Periksa lagi nilainya di berkas .env.',

            // Ini yang paling mungkin ditemui di tier gratis, jadi pesannya
            // menyebut penyebabnya alih-alih sekadar "gagal".
            429 => 'Kuota Gemini sedang habis. Tier gratis punya batas per menit dan per hari — '
                .'tunggu sebentar lalu coba lagi.',

            500, 502, 503, 504 => 'Layanan Gemini sedang bermasalah. Coba lagi beberapa saat lagi.',
            default => 'Gemini menolak permintaan (kode '.$balasan->status().')'
                .($rinci !== '' ? ': '.$rinci : '.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function uraikan(Response $balasan): array
    {
        $isi = $balasan->json();

        $teks = '';
        foreach ((array) data_get($isi, 'outputs', []) as $blok) {
            if (data_get($blok, 'type') === 'text') {
                $teks .= (string) data_get($blok, 'text', '');
            }
        }

        $data = json_decode(trim($teks), true);

        if (! is_array($data)) {
            throw new GagalMenulis('Jawaban Gemini tidak berbentuk naskah yang bisa dibaca. Coba jalankan sekali lagi.');
        }

        return $data;
    }
}
