<?php

namespace App\Services\Berita;

/**
 * Perintah dan skema yang dipakai bersama oleh semua penulis berita.
 *
 * Ditaruh terpisah karena inilah satu-satunya tempat batas faktual itu
 * ditegakkan: model tidak boleh menambah nama, angka, tanggal, tempat, atau
 * lembaga yang tidak ada di bahan, dan tidak boleh mengarang kutipan.
 * Kalau tiap penyedia menyimpan salinan prompt sendiri, cepat atau lambat
 * salah satunya tertinggal saat aturannya diperketat — dan tidak ada yang
 * menyadarinya sampai ada berita yang memuat nama pejabat yang tidak pernah
 * hadir.
 */
class PromptBerita
{
    public static function sistem(): string
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

        - Tepat tiga alternatif dengan sudut yang benar-benar berbeda, bukan variasi susunan kata.
        - Maksimal 80 karakter, tanpa tanda seru, dan tidak seluruhnya huruf kapital.
        TEKS;
    }

    public static function permintaan(BahanBerita $bahan): string
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

    /**
     * Skema naskah dalam bentuk yang diterima kedua penyedia.
     *
     * Sengaja hanya memakai kata kunci JSON Schema yang paling dasar — type,
     * properties, items, description, required. Dukungan skema tiap penyedia
     * berbeda-beda di luar itu, dan kata kunci yang tidak dikenal ditolak
     * sebagai galat 400, bukan diabaikan diam-diam. Jumlah judul yang
     * diharapkan disebutkan di description, lalu tetap ditoleransi di PHP
     * kalau modelnya memberi lebih atau kurang.
     *
     * @return array<string, mixed>
     */
    public static function skema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'judul' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Tepat tiga judul alternatif dengan sudut berbeda, masing-masing maksimal 80 karakter.',
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
        ];
    }
}
