<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Baris lama yang memakai aksen 'tinta' dialihkan ke 'emas'.
     *
     * Nilainya di-cast menjadi enum saat model dimuat, jadi nilai yang tidak
     * lagi dikenal bukan sekadar tampil aneh — ia melempar pengecualian dan
     * membuat seluruh halaman arsip gagal dibuka.
     */
    public function up(): void
    {
        DB::table('thumbnail')->where('aksen', 'tinta')->update(['aksen' => 'emas']);
    }

    public function down(): void
    {
        DB::table('thumbnail')->where('aksen', 'emas')->update(['aksen' => 'tinta']);
    }
};
