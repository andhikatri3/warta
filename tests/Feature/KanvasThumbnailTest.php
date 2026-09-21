<?php

namespace Tests\Feature;

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

    private function render(UkuranThumbnail $ukuran, array $foto): string
    {
        return Blade::render(
            '<x-kanvas-thumbnail :ukuran="$ukuran" :foto="$foto" judul="Judul percobaan" />',
            ['ukuran' => $ukuran, 'foto' => $foto],
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

    public function test_judul_selalu_menumpuk_foto(): void
    {
        // Tidak ada lagi varian pita: judul ditumpuk di atas kolase berapa pun
        // jumlah fotonya, dan yang membuatnya terbaca adalah garis tepi huruf.
        foreach ([1, 3] as $jumlah) {
            $html = $this->render(UkuranThumbnail::Facebook, $this->foto($jumlah));

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

    public function test_akun_resmi_desa_tampil_di_kanvas(): void
    {
        config(['warta.sosmed' => [
            ['ikon' => 'facebook', 'akun' => 'penunggul.id'],
            ['ikon' => 'instagram', 'akun' => '@penunggul.id'],
            ['ikon' => 'web', 'akun' => 'penunggul.desa.id'],
        ]]);

        $html = $this->render(UkuranThumbnail::Facebook, $this->foto(1));

        $this->assertStringContainsString('penunggul.id', $html);
        $this->assertStringContainsString('@penunggul.id', $html);
        $this->assertStringContainsString('penunggul.desa.id', $html);

        // Ikonnya ikut tergambar, bukan hanya teksnya.
        $this->assertSame(3, substr_count($html, '<svg'), 'Tiap akun harus punya ikonnya sendiri');
    }

    public function test_akun_tanpa_isi_tidak_menyisakan_ikon_yatim(): void
    {
        config(['warta.sosmed' => []]);

        $html = $this->render(UkuranThumbnail::Facebook, $this->foto(1));

        $this->assertStringNotContainsString('<svg', $html);
    }

    /**
     * Aksen pernah diam-diam menjadi hiasan tanpa fungsi.
     *
     * Saat lencana label dibuang dari kanvas, satu-satunya sisa pemakai aksen
     * adalah garis tepi huruf model Ceria — jadi pada tiga model lainnya
     * memilih warna tidak mengubah apa pun, tanpa galat dan tanpa tanda.
     * Tes ini memastikan tiap model punya sesuatu yang benar-benar berubah.
     */
    public function test_aksen_mengubah_kanvas_pada_semua_model_teks(): void
    {
        foreach (\App\Enums\ModelTeks::cases() as $model) {
            $merah = $this->renderPenuh($model, \App\Enums\Aksen::Merah);
            $hijau = $this->renderPenuh($model, \App\Enums\Aksen::Hijau);

            $this->assertNotSame(
                $merah,
                $hijau,
                "Mengganti aksen tidak mengubah apa pun pada model {$model->value}",
            );

            $this->assertStringContainsString('#dc2626', $merah);
            $this->assertStringContainsString('#059669', $hijau);
        }
    }

    private function renderPenuh(\App\Enums\ModelTeks $model, \App\Enums\Aksen $aksen): string
    {
        return Blade::render(
            '<x-kanvas-thumbnail :ukuran="$u" :foto="$f" :model="$m" :aksen="$a" judul="Halo" subjudul="Sub" />',
            ['u' => UkuranThumbnail::Facebook, 'f' => $this->foto(1), 'm' => $model, 'a' => $aksen],
        );
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
