<?php

namespace App\Support;

use App\Enums\BentukKolase;
use App\Enums\UkuranThumbnail;

/**
 * Menentukan susunan grid kolase untuk sebuah ukuran dan jumlah foto.
 *
 * Intinya satu: susunan yang bagus di kanvas lebar menjadi jelek di kanvas
 * jangkung. Di Facebook yang melebar, tiga foto paling enak berdampingan;
 * di Story yang menjulang, tiga foto yang sama harus bertumpuk — kalau tidak,
 * tiap sel jadi pita sempit setinggi setengah meter yang memotong semua wajah.
 *
 * Angka pada komentar tiap cabang adalah rasio sel yang dihasilkan. Foto
 * berita umumnya 1.33 sampai 1.78, jadi susunan dipilih yang selnya paling
 * dekat ke rentang itu, bukan yang paling rapi dipandang sebagai diagram.
 *
 * Kelas Tailwind di sini sengaja ditulis sebagai teks utuh, bukan dirangkai
 * dari potongan, supaya pemindai Tailwind menemukannya di berkas ini.
 *
 * @phpstan-type Susunan array{wadah: string, sel: array<int, string>}
 */
class TataLetakKolase
{
    public const MAKS_FOTO = 4;

    /**
     * @return array{wadah: string, sel: array<int, string>}
     */
    public static function untuk(UkuranThumbnail $ukuran, int $jumlah): array
    {
        $jumlah = max(1, min($jumlah, self::MAKS_FOTO));

        return match ($ukuran->bentuk()) {
            BentukKolase::Lebar => self::lebar($jumlah),
            BentukKolase::Kotak => self::kotak($jumlah),
            BentukKolase::Tinggi => self::tinggi($jumlah),
        };
    }

    /**
     * Area melebar, mis. Facebook 1200x458 (2.62:1).
     *
     * @return array{wadah: string, sel: array<int, string>}
     */
    private static function lebar(int $jumlah): array
    {
        return match ($jumlah) {
            // 2.62 — foto tunggal memang ikut bentuk areanya.
            1 => ['wadah' => 'grid-cols-1 grid-rows-1', 'sel' => ['']],

            // 1.31 masing-masing — paling dekat ke foto 4:3.
            2 => ['wadah' => 'grid-cols-2 grid-rows-1', 'sel' => ['', '']],

            // Foto utama 1.57 di kiri, dua pendamping 2.10 bertumpuk di kanan.
            3 => ['wadah' => 'grid-cols-[1.5fr_1fr] grid-rows-2', 'sel' => ['row-span-2', '', '']],

            // 2.62 masing-masing — melebar, tapi seragam dan tetap terbaca.
            default => ['wadah' => 'grid-cols-2 grid-rows-2', 'sel' => ['', '', '', '']],
        };
    }

    /**
     * Area hampir persegi, mis. 1080x860 (1.26:1).
     *
     * @return array{wadah: string, sel: array<int, string>}
     */
    private static function kotak(int $jumlah): array
    {
        return match ($jumlah) {
            1 => ['wadah' => 'grid-cols-1 grid-rows-1', 'sel' => ['']],

            // Bertumpuk (2.51), bukan bersebelahan: dua kolom di sini hanya
            // menghasilkan dua sel jangkung 0.63 yang memotong kiri-kanan foto.
            2 => ['wadah' => 'grid-cols-1 grid-rows-2', 'sel' => ['', '']],

            // Foto utama melintang penuh di atas (2.22), dua pendamping 1.44.
            3 => ['wadah' => 'grid-cols-2 grid-rows-[1.3fr_1fr]', 'sel' => ['col-span-2', '', '']],

            // 1.26 masing-masing.
            default => ['wadah' => 'grid-cols-2 grid-rows-2', 'sel' => ['', '', '', '']],
        };
    }

    /**
     * Area jangkung, mis. Story 1080x1590 (0.68:1).
     *
     * @return array{wadah: string, sel: array<int, string>}
     */
    private static function tinggi(int $jumlah): array
    {
        return match ($jumlah) {
            1 => ['wadah' => 'grid-cols-1 grid-rows-1', 'sel' => ['']],

            // 1.36 masing-masing.
            2 => ['wadah' => 'grid-cols-1 grid-rows-2', 'sel' => ['', '']],

            // 2.04 masing-masing — di sinilah arahnya membalik dari Facebook.
            3 => ['wadah' => 'grid-cols-1 grid-rows-3', 'sel' => ['', '', '']],

            // 0.68 masing-masing; potret, tapi masih sebanding dengan kanvasnya.
            default => ['wadah' => 'grid-cols-2 grid-rows-2', 'sel' => ['', '', '', '']],
        };
    }
}
