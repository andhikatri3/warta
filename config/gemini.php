<?php

return [

    'api_key' => env('GEMINI_API_KEY'),

    /*
     * Model yang dipakai.
     *
     * Bawaannya Flash, bukan Pro: hanya Flash yang ada di tier gratis — model
     * Pro dicabut dari tier itu pada April 2026. Kuota gratisnya sekitar 1.500
     * permintaan sehari, jauh di atas kebutuhan satu desa.
     *
     * Google memperbarui jajaran modelnya cukup sering. Kalau AI Studio
     * menunjukkan Flash yang lebih baru tersedia di tiermu, ganti di sini saja
     * — kuota persisnya memang hanya bisa dilihat di
     * aistudio.google.com/rate-limit, bukan di dokumentasi.
     */
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
     * Revisi API yang dipaku lewat header Api-Revision.
     *
     * Interactions API sudah pernah berubah bentuk secara memutus. Memaku
     * revisinya berarti perubahan berikutnya tidak akan merusak aplikasi ini
     * tanpa ada yang menyentuh kodenya — kita yang memilih kapan pindah.
     */
    'revisi' => env('GEMINI_API_REVISION', '2026-05-20'),

    'timeout' => (int) env('GEMINI_TIMEOUT', 180),

];
