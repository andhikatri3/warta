<?php

return [

    /*
     * Akun resmi yang dicantumkan di bawah subjudul tiap thumbnail.
     *
     * Ditaruh di konfigurasi, bukan di formulir, karena nilainya sama untuk
     * semua thumbnail dan hampir tidak pernah berubah — mengetiknya ulang tiap
     * kali hanya membuka peluang salah ketik yang baru ketahuan setelah
     * gambarnya tersebar.
     *
     * Urutan larik menentukan urutan tampilnya. 'ikon' menunjuk ke komponen
     * Blade di resources/views/components/ikon.
     *
     * Akun berisi kosong TIDAK dibuang dari sini: ia tetap mendapat kolom di
     * formulir (supaya bisa diisi per thumbnail), dan baru disaring saat
     * kanvas disusun — lihat App\Support\AkunSosmed::susun(). TikTok dan
     * YouTube sengaja kosong secara bawaan, jadi tidak tampil sampai diisi.
     */
    'sosmed' => [
        ['ikon' => 'facebook', 'akun' => env('WARTA_FACEBOOK', 'penunggul.id')],
        ['ikon' => 'instagram', 'akun' => env('WARTA_INSTAGRAM', '@penunggul.id')],
        ['ikon' => 'tiktok', 'akun' => env('WARTA_TIKTOK', '')],
        ['ikon' => 'youtube', 'akun' => env('WARTA_YOUTUBE', '')],
        ['ikon' => 'web', 'akun' => env('WARTA_WEB', 'penunggul.desa.id')],
    ],

    /*
     * Logo yang dipakai kalau thumbnail tidak mengunggah logonya sendiri.
     *
     * Alamatnya sengaja relatif terhadap akar situs, bukan asset(): asset()
     * menghasilkan alamat absolut dari APP_URL, dan gambar dari asal yang
     * berbeda dengan halaman menajiskan kanvas — tombol unduh lalu gagal
     * total. Alasan yang sama dengan URL disk public di config/filesystems.php.
     *
     * Berkasnya PNG transparan 720x252, sudah dikecilkan dari aslinya:
     * logo di kanvas paling tinggi sekitar 63 piksel, jadi berkas 2000 piksel
     * hanya memperlambat penangkapan tanpa menambah ketajaman.
     *
     * Kosongkan (WARTA_LOGO_BAWAAN=) untuk thumbnail tanpa logo bawaan.
     */
    'logo_bawaan' => env('WARTA_LOGO_BAWAAN', '/img/logo-bawaan.png'),

];
