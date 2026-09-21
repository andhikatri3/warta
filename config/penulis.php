<?php

use App\Services\Berita\PenulisClaude;
use App\Services\Berita\PenulisGemini;

return [

    /*
     * Penyedia yang dipakai untuk menulis berita.
     *
     * Bawaannya 'gemini' karena tier gratisnya cukup untuk pemakaian desa.
     * Ganti ke 'claude' lewat PENULIS_BERITA di .env kalau ingin naskah yang
     * lebih baik untuk berita penting — prompt, skema, dan seluruh antarmuka
     * dipakai bersama, jadi yang berubah hanya penyedianya.
     */
    'driver' => env('PENULIS_BERITA', 'gemini'),

    'penyedia' => [
        'gemini' => PenulisGemini::class,
        'claude' => PenulisClaude::class,
    ],

];
