<?php

namespace App\Enums;

enum NadaBerita: string
{
    case Lugas = 'lugas';
    case Resmi = 'resmi';
    case Hangat = 'hangat';

    public function label(): string
    {
        return match ($this) {
            self::Lugas => 'Lugas',
            self::Resmi => 'Resmi',
            self::Hangat => 'Hangat',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Lugas => 'Gaya pemberitaan biasa',
            self::Resmi => 'Siaran pers pemerintahan',
            self::Hangat => 'Ramah, dekat dengan warga',
        };
    }

    public function arahan(): string
    {
        return match ($this) {
            self::Lugas => 'Gaya berita harian: kalimat pendek, kata kerja aktif, tanpa bunga-bunga.',
            self::Resmi => 'Gaya siaran pers resmi: tertib, menyebut jabatan lengkap, menghindari ungkapan santai.',
            self::Hangat => 'Gaya ramah untuk warga desa: kalimat sederhana, hangat, tetap sopan dan tidak berlebihan.',
        };
    }
}
