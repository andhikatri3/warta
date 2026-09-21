<?php

return [

    'api_key' => env('ANTHROPIC_API_KEY'),

    'model' => env('ANTHROPIC_MODEL', 'claude-opus-5'),

    /*
     * Seberapa dalam model berpikir sebelum menulis.
     *
     * Ditahan di 'medium', bukan bawaan 'high', karena tugasnya menyusun fakta
     * yang sudah diberikan menjadi prosa — pekerjaan mengarang, bukan menalar.
     * Naikkan ke 'high' kalau hasilnya terasa dangkal; turunkan ke 'low' kalau
     * menunggunya terasa lama. Ini tuas yang paling layak disetel duluan.
     */
    'effort' => env('ANTHROPIC_EFFORT', 'medium'),

    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 8000),

    /*
     * Batas waktu satu permintaan. Menulis berita utuh bisa memakan puluhan
     * detik, jauh melewati batas wajar permintaan HTTP biasa.
     */
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 180),

];
