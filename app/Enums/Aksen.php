<?php

namespace App\Enums;

/**
 * Warna aksen judul: ikon akun resmi, dan bagian berwarna pada tiap model teks
 * (garis tepi Ceria, bayangan Miring dan Timbul, blok latar Blok, pendar
 * Pendar).
 *
 * Semuanya harus terbaca di atas foto yang digelapkan gradien, jadi tidak ada
 * warna yang mendekati hitam di sini. Pilihan 'tinta' yang dulu ada dibuang
 * saat varian pita putih dicabut: di atas gradien gelap ia praktis tak
 * terlihat, dan pilihan yang tak terlihat sama saja dengan pilihan yang rusak.
 *
 * Nilainya dikembalikan sebagai heks, bukan kelas Tailwind, karena dipakai
 * lewat atribut style di dalam kanvas. Kanvas dirender ulang menjadi gambar,
 * dan gambar tidak ikut memuat lembar gaya — jadi warna yang menentukan hasil
 * akhir lebih aman ditulis langsung pada elemennya.
 */
enum Aksen: string
{
    case Biru = 'biru';
    case Toska = 'toska';
    case Hijau = 'hijau';
    case Emas = 'emas';
    case Jingga = 'jingga';
    case Merah = 'merah';
    case MerahMuda = 'merah-muda';
    case Ungu = 'ungu';
    case Putih = 'putih';

    public function heks(): string
    {
        return match ($this) {
            self::Biru => '#2563eb',
            self::Toska => '#06b6d4',
            self::Hijau => '#059669',
            self::Emas => '#f59e0b',
            self::Jingga => '#ea580c',
            self::Merah => '#dc2626',
            self::MerahMuda => '#ec4899',
            self::Ungu => '#7c3aed',
            self::Putih => '#f1f5f9',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Biru => 'Biru',
            self::Toska => 'Toska',
            self::Hijau => 'Hijau',
            self::Emas => 'Emas',
            self::Jingga => 'Jingga',
            self::Merah => 'Merah',
            self::MerahMuda => 'Merah muda',
            self::Ungu => 'Ungu',
            self::Putih => 'Putih',
        };
    }
}
