<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Varian pita dicabut; judul selalu ditumpuk di atas foto.
     *
     * Pilihan yang hanya punya satu nilai bukan pilihan, dan cabang render yang
     * tidak pernah tercapai lama-lama membusuk tanpa ada yang menyadarinya.
     */
    public function up(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->dropColumn('gaya');
        });
    }

    public function down(): void
    {
        Schema::table('thumbnail', function (Blueprint $table) {
            $table->string('gaya', 20)->default('overlay')->after('subjudul');
        });
    }
};
