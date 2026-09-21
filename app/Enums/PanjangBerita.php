<?php

namespace App\Enums;

enum PanjangBerita: string
{
    case Pendek = 'pendek';
    case Sedang = 'sedang';
    case Panjang = 'panjang';

    public function label(): string
    {
        return match ($this) {
            self::Pendek => 'Pendek',
            self::Sedang => 'Sedang',
            self::Panjang => 'Panjang',
        };
    }

    public function kisaran(): string
    {
        return match ($this) {
            self::Pendek => '150–200 kata',
            self::Sedang => '250–350 kata',
            self::Panjang => '450–600 kata',
        };
    }

    public function arahan(): string
    {
        return match ($this) {
            self::Pendek => 'Sekitar 150 sampai 200 kata, tiga sampai empat paragraf.',
            self::Sedang => 'Sekitar 250 sampai 350 kata, lima sampai enam paragraf.',
            self::Panjang => 'Sekitar 450 sampai 600 kata, tujuh sampai sembilan paragraf.',
        };
    }
}
