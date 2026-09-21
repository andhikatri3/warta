<?php

namespace App\Enums;

/**
 * Model teks judul: gabungan huruf dan perlakuannya (garis tepi, bayangan,
 * gradien, blok latar).
 *
 * Perlakuannya sendiri tinggal di CSS, dipilih lewat atribut data-model pada
 * kanvas. Yang tidak bisa dijangkau utility Tailwind — -webkit-text-stroke,
 * paint-order, gradien yang dipotong bentuk huruf, dan latar yang menempel
 * per baris — ditulis sekali di resources/css/app.css, bukan diulang sebagai
 * nilai arbitrer di tiap tempat.
 *
 * Garis tepi tebal bukan hiasan. Justru itu yang membuat judul boleh ditumpuk
 * di atas kolase: di atas foto apa pun, terang maupun ramai, huruf berpinggir
 * tetap terbaca.
 *
 * Tiap model wajib memakai var(--aksen) untuk sesuatu. Aksen pernah diam-diam
 * menjadi pilihan tanpa akibat karena satu-satunya pemakainya terhapus; kalau
 * menambah model baru, pastikan warnanya benar-benar mengubah sesuatu.
 */
enum ModelTeks: string
{
    case Ceria = 'ceria';
    case Tegas = 'tegas';
    case Miring = 'miring';
    case Blok = 'blok';
    case Timbul = 'timbul';
    case Pendar = 'pendar';
    case Agung = 'agung';
    case Bersih = 'bersih';

    public function label(): string
    {
        return match ($this) {
            self::Ceria => 'Ceria',
            self::Tegas => 'Tegas',
            self::Miring => 'Miring',
            self::Blok => 'Blok',
            self::Timbul => 'Timbul',
            self::Pendar => 'Pendar',
            self::Agung => 'Agung',
            self::Bersih => 'Bersih',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Ceria => 'Bulat, garis tepi warna aksen',
            self::Tegas => 'Kapital padat, garis tepi hitam',
            self::Miring => 'Kapital condong, bayangan aksen',
            self::Blok => 'Tiap baris berlatar blok aksen',
            self::Timbul => 'Bayangan padat, terkesan timbul',
            self::Pendar => 'Ramping, berpendar warna aksen',
            self::Agung => 'Serif emas bergradien',
            self::Bersih => 'Polos, hanya bayangan tipis',
        };
    }

    /**
     * Contoh huruf untuk pemilihnya, supaya perbedaan modelnya terlihat
     * sebelum dipilih.
     */
    public function contoh(): string
    {
        return match ($this) {
            self::Tegas, self::Miring, self::Blok, self::Pendar => 'AA',
            default => 'Aa',
        };
    }

    public function cocokUntuk(): string
    {
        return match ($this) {
            self::Ceria => 'Posyandu, PKK, kegiatan anak',
            self::Tegas => 'Lomba, upacara, kunjungan dinas',
            self::Miring => 'Olahraga, karang taruna, lomba desa',
            self::Blok => 'Pengumuman, imbauan, agenda',
            self::Timbul => 'Festival, bazar, hari jadi desa',
            self::Pendar => 'Acara malam, panggung, pentas seni',
            self::Agung => 'Pengajian, maulid, hari besar',
            self::Bersih => 'Siaran pers, pengumuman resmi',
        };
    }
}
