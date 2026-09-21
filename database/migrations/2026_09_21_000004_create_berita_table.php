<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->index();
            $table->text('lead');
            $table->longText('isi');
            $table->string('meta', 300)->nullable();
            $table->string('nada', 20)->default('lugas');
            $table->string('panjang', 20)->default('sedang');

            // Bahan 5W+1H disimpan apa adanya di samping naskah jadinya.
            // Tanpa ini tidak ada cara memeriksa ulang dari mana sebuah
            // kalimat berasal, dan tidak ada cara menyusun ulang berita
            // dengan gaya lain tanpa mengetik semuanya dari awal.
            $table->json('bahan');

            // Dua judul yang tidak dipilih ikut disimpan: memilih ulang judul
            // jauh lebih murah daripada memanggil model sekali lagi.
            $table->json('judul_alternatif')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
