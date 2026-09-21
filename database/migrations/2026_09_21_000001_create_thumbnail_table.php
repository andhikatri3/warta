<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thumbnail', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('subjudul')->nullable();
            $table->string('label', 40)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('gaya', 20)->default('pita-terang');
            $table->string('aksen', 20)->default('biru');
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thumbnail');
    }
};
