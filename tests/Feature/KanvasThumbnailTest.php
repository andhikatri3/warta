<?php

namespace Tests\Feature;

use App\Enums\GayaThumbnail;
use App\Enums\UkuranThumbnail;
use App\Support\TataLetakKolase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class KanvasThumbnailTest extends TestCase
{
    /** @return array<int, array{url: string, fokus_x: int, fokus_y: int}> */
    private function foto(int $jumlah): array
    {
        return collect(range(1, $jumlah))
            ->map(fn (int $i) => ['url' => "/storage/foto-{$i}.jpg", 'fokus_x' => 50, 'fokus_y' => 50])
            ->all();
    }

    private function render(UkuranThumbnail $ukuran, array $foto, ?GayaThumbnail $gaya = null): string
    {
        return Blade::render(
            '<x-kanvas-thumbnail :ukuran="$ukuran" :foto="$foto" :gaya="$gaya" judul="Judul percobaan" />',
            ['ukuran' => $ukuran, 'foto' => $foto, 'gaya' => $gaya],
        );
    }

    public function test_kanvas_memakai_ukuran_piksel_sebenarnya(): void
    {
        foreach (UkuranThumbnail::semua() as $ukuran) {
            $html = $this->render($ukuran, $this->foto(1));

            $this->assertStringContainsString(
                "width: {$ukuran->lebar()}px; height: {$ukuran->tinggi()}px;",
                $html,
            );
            $this->assertStringContainsString('data-lebar="'.$ukuran->lebar().'"', $html);
        }
    }

    public function test_overlay_dipertahankan_walau_fotonya_kolase(): void
    {
        $satu = $this->render(UkuranThumbnail::Facebook, $this->foto(1), GayaThumbnail::Overlay);
        $banyak = $this->render(UkuranThumbnail::Facebook, $this->foto(3), GayaThumbnail::Overlay);

        // Gradien dan kelas hiasan hanya ada pada cabang overlay. Dulu overlay
        // diturunkan paksa ke pita begitu fotonya lebih dari satu; sekarang
        // tidak lagi, karena judul bergaris tepi tetap terbaca di atas kolase.
        foreach ([$satu, $banyak] as $html) {
            $this->assertStringContainsString('linear-gradient', $html);
            $this->assertStringContainsString('judul-hias', $html);
        }
    }

    public function test_model_teks_menentukan_atribut_kanvas_dan_warna_aksen(): void
    {
        $html = Blade::render(
            '<x-kanvas-thumbnail :ukuran="$u" :foto="$f" :model="$m" :aksen="$a" judul="Halo" />',
            [
                'u' => UkuranThumbnail::Facebook,
                'f' => $this->foto(1),
                'm' => \App\Enums\ModelTeks::Agung,
                'a' => \App\Enums\Aksen::Merah,
            ],
        );

        $this->assertStringContainsString('data-model="agung"', $html);
        $this->assertStringContainsString('--aksen: #dc2626;', $html);
    }

    public function test_pita_memakai_huruf_model_tanpa_hiasannya(): void
    {
        $html = $this->render(UkuranThumbnail::Facebook, $this->foto(1), GayaThumbnail::PitaTerang);

        // Hurufnya ikut model, tetapi garis tepi tidak boleh masuk ke pita:
        // aturan warnanya akan mengalahkan kelas Tailwind dan judul putih
        // mendarat di atas pita putih.
        $this->assertStringContainsString('judul-kanvas', $html);
        $this->assertStringNotContainsString('judul-hias', $html);
    }

    public function test_akun_resmi_desa_tampil_di_kanvas(): void
    {
        config(['warta.sosmed' => [
            ['ikon' => 'facebook', 'akun' => 'penunggul.id'],
            ['ikon' => 'instagram', 'akun' => '@penunggul.id'],
            ['ikon' => 'web', 'akun' => 'penunggul.desa.id'],
        ]]);

        foreach ([GayaThumbnail::Overlay, GayaThumbnail::PitaTerang] as $gaya) {
            $html = $this->render(UkuranThumbnail::Facebook, $this->foto(1), $gaya);

            $this->assertStringContainsString('penunggul.id', $html);
            $this->assertStringContainsString('@penunggul.id', $html);
            $this->assertStringContainsString('penunggul.desa.id', $html);

            // Ikonnya ikut tergambar, bukan hanya teksnya.
            $this->assertSame(3, substr_count($html, '<svg'), 'Tiap akun harus punya ikonnya sendiri');
        }
    }

    public function test_akun_tanpa_isi_tidak_menyisakan_ikon_yatim(): void
    {
        config(['warta.sosmed' => []]);

        $html = $this->render(UkuranThumbnail::Facebook, $this->foto(1));

        $this->assertStringNotContainsString('<svg', $html);
    }

    public function test_foto_melebihi_batas_tidak_ikut_dirender(): void
    {
        $html = $this->render(UkuranThumbnail::Facebook, $this->foto(7));

        $this->assertSame(
            TataLetakKolase::MAKS_FOTO,
            substr_count($html, '<img'),
            'Jumlah gambar yang dirender harus terpotong di batas maksimum',
        );
    }

    public function test_titik_fokus_diteruskan_sebagai_object_position(): void
    {
        $html = $this->render(UkuranThumbnail::Facebook, [
            ['url' => '/storage/a.jpg', 'fokus_x' => 18, 'fokus_y' => 72],
        ]);

        $this->assertStringContainsString('object-position: 18% 72%;', $html);
    }

    public function test_tanpa_foto_kanvas_tetap_terbentuk_dengan_penanda_kosong(): void
    {
        $html = $this->render(UkuranThumbnail::Story, []);

        $this->assertStringContainsString('Belum ada foto', $html);
        $this->assertStringContainsString('data-kanvas="story"', $html);
    }

    public function test_pemangkas_baris_judul_ikut_ukuran_kanvas(): void
    {
        $this->assertStringContainsString('line-clamp-2', $this->render(UkuranThumbnail::Facebook, $this->foto(1)));
        $this->assertStringContainsString('line-clamp-3', $this->render(UkuranThumbnail::Story, $this->foto(1)));
    }
}
