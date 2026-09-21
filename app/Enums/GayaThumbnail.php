<?php

namespace App\Enums;

/**
 * Varian desain thumbnail.
 *
 * Pita menaruh judul di bidang solid di bawah kolase; Overlay menumpuknya di
 * atas foto. Overlay adalah bawaannya.
 *
 * Dulu Overlay diturunkan paksa ke Pita begitu fotonya lebih dari satu, karena
 * judul polos bisa mendarat tepat di atas wajah atau bidang terang. Penjagaan
 * itu dicabut setelah ada ModelTeks: judul bergaris tepi tebal terbaca di atas
 * foto apa pun, dan justru itulah cara desain poster desa menaruh teksnya.
 */
enum GayaThumbnail: string
{
    case PitaTerang = 'pita-terang';
    case PitaGelap = 'pita-gelap';
    case Overlay = 'overlay';

    public function label(): string
    {
        return match ($this) {
            self::PitaTerang => 'Pita terang',
            self::PitaGelap => 'Pita gelap',
            self::Overlay => 'Overlay',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::PitaTerang => 'Judul di bidang putih',
            self::PitaGelap => 'Judul di bidang tinta',
            self::Overlay => 'Judul di atas foto',
        };
    }

    public function berpita(): bool
    {
        return $this !== self::Overlay;
    }
}
