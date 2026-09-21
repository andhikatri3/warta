<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Label dan tanggal dicabut dari kanvas.
     *
     * Tempatnya di bawah subjudul kini ditempati akun resmi desa, yang datang
     * dari konfigurasi dan bukan dari basis data. Kolomnya ikut dibuang alih-alih
     * ditinggal kosong: kolom yang tidak dipakai lama-lama tampak seperti fitur
     * yang rusak, bukan fitur yang sengaja dihapus.
     */
    public function up(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->dropColumn(['label', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->string('label', 40)->nullable()->after('subjudul');
            $table->date('tanggal')->nullable()->after('label');
        });
    }
};
