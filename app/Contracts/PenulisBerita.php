<?php

namespace App\Contracts;

use App\Services\Berita\BahanBerita;
use App\Services\Berita\NaskahBerita;

/**
 * Dipisahkan menjadi kontrak supaya pengujian tidak pernah memanggil layanan
 * berbayar, dan supaya penyedia modelnya bisa diganti tanpa menyentuh
 * komponen Livewire yang memakainya.
 */
interface PenulisBerita
{
    /**
     * @throws \App\Services\Berita\GagalMenulis
     */
    public function tulis(BahanBerita $bahan): NaskahBerita;
}
