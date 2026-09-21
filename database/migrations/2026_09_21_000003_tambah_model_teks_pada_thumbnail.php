<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->string('model_teks', 20)->default('ceria')->after('gaya');

            // Judul kini bertumpuk di atas foto sebagai bawaan. Itu baru boleh
            // menjadi bawaan setelah ada model teks bergaris tepi: huruf
            // berpinggir tebal tetap terbaca di atas foto seramai apa pun,
            // sementara huruf polos dulu harus mengungsi ke pita sendiri.
            $table->string('gaya', 20)->default('overlay')->change();
        });
    }

    public function down(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->dropColumn('model_teks');
            $table->string('gaya', 20)->default('pita-terang')->change();
        });
    }
};
