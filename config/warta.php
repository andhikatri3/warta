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
     */
    'sosmed' => array_values(array_filter([
        ['ikon' => 'facebook', 'akun' => env('WARTA_FACEBOOK', 'penunggul.id')],
        ['ikon' => 'instagram', 'akun' => env('WARTA_INSTAGRAM', '@penunggul.id')],
        ['ikon' => 'web', 'akun' => env('WARTA_WEB', 'penunggul.desa.id')],
    ], fn (array $s) => trim((string) $s['akun']) !== '')),

];
