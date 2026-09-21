<?php

namespace Tests\Dukungan;

use App\Contracts\PenulisBerita;
use App\Services\Berita\BahanBerita;
use App\Services\Berita\GagalMenulis;
use App\Services\Berita\NaskahBerita;

/**
 * Penulis tiruan untuk pengujian.
 *
 * Merekam bahan yang diterimanya supaya tes bisa memastikan seluruh ruas
 * 5W+1H benar-benar sampai ke penulis, dan bisa disuruh gagal supaya jalur
 * penanganan galat ikut teruji.
 */
class PenulisTiruan implements PenulisBerita
{
    public ?BahanBerita $bahanTerakhir = null;

    public int $jumlahPanggilan = 0;

    public function __construct(
        private ?NaskahBerita $naskah = null,
        private ?string $galat = null,
    ) {}

    public static function yangGagal(string $pesan): self
    {
        return new self(galat: $pesan);
    }

    public function tulis(BahanBerita $bahan): NaskahBerita
    {
        $this->bahanTerakhir = $bahan;
        $this->jumlahPanggilan++;

        if ($this->galat !== null) {
            throw new GagalMenulis($this->galat);
        }

        return $this->naskah ?? new NaskahBerita(
            judul: [
                'Posyandu baru diresmikan di Desa Penunggul',
                'Warga Penunggul kini punya gedung Posyandu sendiri',
                'Gedung Posyandu Penunggul mulai melayani warga',
            ],
            lead: 'Pemerintah Desa Penunggul meresmikan gedung Posyandu baru di Dusun Krajan pada Sabtu, 21 September 2026.',
            isi: "Peresmian dihadiri Camat Nguling dan sekitar delapan puluh warga.\n\nGedung lama sudah tidak muat menampung peserta.",
            meta: 'Pemerintah Desa Penunggul meresmikan gedung Posyandu baru di Dusun Krajan.',
            slug: 'posyandu-baru-diresmikan-di-desa-penunggul',
        );
    }
}
