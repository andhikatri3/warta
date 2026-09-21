<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thumbnail_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thumbnail_id')->constrained('thumbnail')->cascadeOnDelete();
            $table->string('path');

            // Urutan menentukan penempatan di grid: foto pertama mengisi sel
            // utama, sisanya menyusul. Jadi menggeser urutan sama dengan
            // mengubah komposisi, bukan sekadar merapikan daftar.
            $table->unsignedTinyInteger('urutan')->default(0);

            // Titik fokus dalam persen terhadap lebar dan tinggi foto asli,
            // dipakai sebagai object-position. Di kolase tiap sel adalah
            // potongan kecil berbentuk tak terduga, dan potongan dari tengah
            // hampir selalu memenggal orang yang berdiri di tepi bingkai.
            $table->unsignedTinyInteger('fokus_x')->default(50);
            $table->unsignedTinyInteger('fokus_y')->default(50);

            $table->timestamps();

            $table->index(['thumbnail_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thumbnail_foto');
    }
};
