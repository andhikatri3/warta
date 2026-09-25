<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teks akun sosmed yang diubah per thumbnail (null = ikut config/warta.php),
     * dan aksen bawaan pindah dari biru ke hijau — warna situs desa Penunggul.
     * Baris lama tidak disentuh: yang sudah memilih biru tetap biru.
     */
    public function up(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->json('sosmed')->nullable()->after('logo');
            $table->string('aksen', 20)->default('hijau')->change();
        });
    }

    public function down(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->dropColumn('sosmed');
            $table->string('aksen', 20)->default('biru')->change();
        });
    }
};
