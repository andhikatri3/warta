<?php

namespace Tests\Unit;

use App\Enums\UkuranThumbnail;
use App\Support\TataLetakKolase;
use PHPUnit\Framework\TestCase;

class TataLetakKolaseTest extends TestCase
{
    /**
     * Inti dari seluruh fitur kolase: susunan tidak sekadar diperkecil dari
     * satu ukuran ke ukuran lain, arahnya membalik. Kalau tes ini jatuh,
     * Story akan memakai susunan Facebook dan tiap selnya menjadi pita sempit
     * yang memotong semua wajah.
     */
    public function test_tiga_foto_bersebelahan_di_kanvas_lebar_dan_bertumpuk_di_kanvas_jangkung(): void
    {
        $facebook = TataLetakKolase::untuk(UkuranThumbnail::Facebook, 3);
        $story = TataLetakKolase::untuk(UkuranThumbnail::Story, 3);

        // Facebook: dua kolom, foto utama melintang dua baris di kolom kiri.
        $this->assertStringContainsString('grid-cols-[1.5fr_1fr]', $facebook['wadah']);
        $this->assertSame('row-span-2', $facebook['sel'][0]);

        // Story: satu kolom, tiga baris bertumpuk.
        $this->assertStringContainsString('grid-cols-1', $story['wadah']);
        $this->assertStringContainsString('grid-rows-3', $story['wadah']);

        $this->assertNotSame($facebook['wadah'], $story['wadah']);
    }

    public function test_dua_foto_bersebelahan_di_facebook_tapi_bertumpuk_di_persegi(): void
    {
        // Di area 1080x860, dua kolom menghasilkan sel jangkung 0.63 yang
        // memotong kiri-kanan foto; bertumpuk jauh lebih dekat ke foto berita.
        $this->assertStringContainsString('grid-cols-2', TataLetakKolase::untuk(UkuranThumbnail::Facebook, 2)['wadah']);
        $this->assertStringContainsString('grid-rows-2', TataLetakKolase::untuk(UkuranThumbnail::Persegi, 2)['wadah']);
    }

    public function test_jumlah_foto_dibatasi_dan_selnya_selalu_sebanyak_foto(): void
    {
        foreach (UkuranThumbnail::semua() as $ukuran) {
            foreach ([1, 2, 3, 4] as $jumlah) {
                $letak = TataLetakKolase::untuk($ukuran, $jumlah);

                $this->assertCount(
                    $jumlah,
                    $letak['sel'],
                    "Sel tidak sebanyak foto untuk {$ukuran->value} dengan {$jumlah} foto",
                );
            }
        }
    }

    public function test_jumlah_di_luar_rentang_dijepit_bukan_menghasilkan_susunan_kosong(): void
    {
        $nol = TataLetakKolase::untuk(UkuranThumbnail::Facebook, 0);
        $berlebih = TataLetakKolase::untuk(UkuranThumbnail::Facebook, 9);

        $this->assertCount(1, $nol['sel']);
        $this->assertCount(TataLetakKolase::MAKS_FOTO, $berlebih['sel']);
        $this->assertNotSame('', $nol['wadah']);
    }
}
