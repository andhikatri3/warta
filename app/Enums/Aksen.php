<?php

namespace App\Enums;

/**
 * Warna aksen judul: palang di atas judul dan ikon akun resmi.
 *
 * Semuanya harus terbaca di atas foto yang digelapkan gradien, jadi tidak ada
 * warna yang mendekati hitam di sini. Pilihan 'tinta' yang dulu ada dibuang
 * saat varian pita putih dicabut: di atas gradien gelap ia praktis tak
 * terlihat, dan pilihan yang tak terlihat sama saja dengan pilihan yang
 * rusak.
 *
 * Nilainya dikembalikan sebagai heks, bukan kelas Tailwind, karena dipakai
 * lewat atribut style di dalam kanvas. Kanvas dirender ulang menjadi gambar,
 * dan gambar tidak ikut memuat lembar gaya — jadi warna yang menentukan hasil
 * akhir lebih aman ditulis langsung pada elemennya.
 */
enum Aksen: string
{
    case Biru = 'biru';
    case Merah = 'merah';
    case Hijau = 'hijau';
    case Jingga = 'jingga';
    case Emas = 'emas';

    public function heks(): string
    {
        return match ($this) {
            self::Biru => '#2563eb',
            self::Merah => '#dc2626',
            self::Hijau => '#059669',
            self::Jingga => '#ea580c',
            self::Emas => '#f59e0b',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Biru => 'Biru',
            self::Merah => 'Merah',
            self::Hijau => 'Hijau',
            self::Jingga => 'Jingga',
            self::Emas => 'Emas',
        };
    }
}
