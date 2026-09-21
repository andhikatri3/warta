<?php

namespace App\Enums;

/**
 * Varian desain thumbnail.
 *
 * Pita menaruh judul di bidang solid di bawah kolase; Overlay menumpuknya di
 * atas foto dengan gradien. Overlay hanya rapi kalau fotonya satu dan bagian
 * bawahnya lapang — di kolase, sudut tempat judul jatuh berbeda-beda tiap
 * gambar dan judul bisa mendarat tepat di atas wajah atau bidang terang.
 * Karena itu Overlay ditandai tidak cocok untuk kolase, dan pemilihnya
 * dinonaktifkan begitu foto lebih dari satu.
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

    public function cocokUntukKolase(): bool
    {
        return $this->berpita();
    }
}
