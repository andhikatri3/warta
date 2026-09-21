<?php

namespace App\Enums;

/**
 * Model teks judul: gabungan huruf dan perlakuannya (garis tepi, bayangan,
 * gradien).
 *
 * Perlakuannya sendiri tinggal di CSS, dipilih lewat atribut data-model pada
 * kanvas. Yang tidak bisa dijangkau utility Tailwind — -webkit-text-stroke,
 * paint-order, dan gradien yang dipotong bentuk huruf — ditulis sekali di
 * resources/css/app.css, bukan diulang sebagai nilai arbitrer di tiap tempat.
 *
 * Garis tepi tebal bukan hiasan. Justru itu yang membuat judul boleh ditumpuk
 * di atas kolase: di atas foto apa pun, terang maupun ramai, huruf berpinggir
 * tetap terbaca. Tanpa perlakuan ini judul harus mengungsi ke pita sendiri.
 */
enum ModelTeks: string
{
    case Ceria = 'ceria';
    case Tegas = 'tegas';
    case Agung = 'agung';
    case Bersih = 'bersih';

    public function label(): string
    {
        return match ($this) {
            self::Ceria => 'Ceria',
            self::Tegas => 'Tegas',
            self::Agung => 'Agung',
            self::Bersih => 'Bersih',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Ceria => 'Bulat, garis tepi warna aksen',
            self::Tegas => 'Kapital padat, garis tepi hitam',
            self::Agung => 'Serif emas bergradien',
            self::Bersih => 'Tanpa garis tepi',
        };
    }

    /**
     * Contoh huruf untuk pemilihnya, supaya perbedaan modelnya terlihat
     * sebelum dipilih.
     */
    public function contoh(): string
    {
        return match ($this) {
            self::Tegas => 'AA',
            default => 'Aa',
        };
    }

    public function cocokUntuk(): string
    {
        return match ($this) {
            self::Ceria => 'Posyandu, PKK, kegiatan anak',
            self::Tegas => 'Lomba, upacara, kunjungan dinas',
            self::Agung => 'Pengajian, maulid, hari besar',
            self::Bersih => 'Siaran pers, pengumuman resmi',
        };
    }
}
