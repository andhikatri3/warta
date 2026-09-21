<?php

namespace App\Enums;

/**
 * Warna aksen pita judul.
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
    case Tinta = 'tinta';

    public function heks(): string
    {
        return match ($this) {
            self::Biru => '#2563eb',
            self::Merah => '#dc2626',
            self::Hijau => '#059669',
            self::Jingga => '#ea580c',
            self::Tinta => '#1e293b',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Biru => 'Biru',
            self::Merah => 'Merah',
            self::Hijau => 'Hijau',
            self::Jingga => 'Jingga',
            self::Tinta => 'Tinta',
        };
    }
}
