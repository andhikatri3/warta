<?php

namespace Tests\Feature;

use App\Enums\GayaThumbnail;
use App\Livewire\Thumbnail\PembuatThumbnail;
use App\Models\Thumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PembuatThumbnailTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_penyusun_terbuka(): void
    {
        $this->get(route('thumbnail.baru'))->assertOk();
    }

    public function test_halaman_riwayat_terbuka(): void
    {
        $this->get(route('thumbnail.riwayat'))->assertOk();
    }

    public function test_judul_wajib_diisi_sebelum_disimpan(): void
    {
        Livewire::test(PembuatThumbnail::class)
            ->set('judul', '')
            ->call('simpan')
            ->assertHasErrors(['judul' => 'required']);

        $this->assertSame(0, Thumbnail::count());
    }

    public function test_menyimpan_membuat_satu_baris_thumbnail(): void
    {
        Livewire::test(PembuatThumbnail::class)
            ->set('judul', 'RSUD Resmikan Gedung Baru')
            ->set('subjudul', 'Melayani 200 pasien per hari')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('thumbnail', [
            'judul' => 'RSUD Resmikan Gedung Baru',
            'subjudul' => 'Melayani 200 pasien per hari',
        ]);
    }

    public function test_menyimpan_dua_kali_memperbarui_baris_yang_sama(): void
    {
        $komponen = Livewire::test(PembuatThumbnail::class)
            ->set('judul', 'Judul pertama')
            ->call('simpan');

        $komponen->set('judul', 'Judul kedua')->call('simpan');

        $this->assertSame(1, Thumbnail::count());
        $this->assertSame('Judul kedua', Thumbnail::first()->judul);
    }

    public function test_urutan_foto_bisa_digeser(): void
    {
        $foto = [
            ['path' => 'a.jpg', 'url' => '/storage/a.jpg', 'fokus_x' => 50, 'fokus_y' => 50],
            ['path' => 'b.jpg', 'url' => '/storage/b.jpg', 'fokus_x' => 50, 'fokus_y' => 50],
            ['path' => 'c.jpg', 'url' => '/storage/c.jpg', 'fokus_x' => 50, 'fokus_y' => 50],
        ];

        Livewire::test(PembuatThumbnail::class)
            ->set('foto', $foto)
            ->call('geserFoto', 2, 0)
            ->assertSet('foto.0.path', 'c.jpg')
            ->assertSet('foto.1.path', 'a.jpg')
            ->assertSet('foto.2.path', 'b.jpg');
    }

    public function test_titik_fokus_dijepit_di_rentang_nol_sampai_seratus(): void
    {
        Livewire::test(PembuatThumbnail::class)
            ->set('foto', [['path' => 'a.jpg', 'url' => '/storage/a.jpg', 'fokus_x' => 50, 'fokus_y' => 50]])
            ->call('aturFokus', 0, -30, 480)
            ->assertSet('foto.0.fokus_x', 0)
            ->assertSet('foto.0.fokus_y', 100);
    }

    public function test_overlay_adalah_bawaan_dan_bertahan_saat_fotonya_kolase(): void
    {
        $komponen = Livewire::test(PembuatThumbnail::class);

        $this->assertSame(GayaThumbnail::Overlay, $komponen->instance()->gayaTerpilih);

        $komponen->set('foto', [
            ['path' => 'a.jpg', 'url' => '/storage/a.jpg', 'fokus_x' => 50, 'fokus_y' => 50],
            ['path' => 'b.jpg', 'url' => '/storage/b.jpg', 'fokus_x' => 50, 'fokus_y' => 50],
        ]);

        $this->assertSame(GayaThumbnail::Overlay, $komponen->instance()->gayaTerpilih);
    }

    public function test_model_teks_ikut_tersimpan_dan_termuat_kembali(): void
    {
        Livewire::test(PembuatThumbnail::class)
            ->set('judul', 'Pengajian Akbar')
            ->set('model', \App\Enums\ModelTeks::Agung->value)
            ->call('simpan')
            ->assertHasNoErrors();

        $tersimpan = Thumbnail::firstOrFail();
        $this->assertSame(\App\Enums\ModelTeks::Agung, $tersimpan->model_teks);

        Livewire::test(PembuatThumbnail::class, ['thumbnail' => $tersimpan])
            ->assertSet('model', 'agung');
    }

    public function test_menyunting_thumbnail_tersimpan_memuat_isinya(): void
    {
        $thumbnail = Thumbnail::create([
            'judul' => 'Judul tersimpan',
        ]);

        $thumbnail->foto()->create([
            'path' => 'thumbnail/a.jpg',
            'urutan' => 0,
            'fokus_x' => 30,
            'fokus_y' => 40,
        ]);

        Livewire::test(PembuatThumbnail::class, ['thumbnail' => $thumbnail->fresh()])
            ->assertSet('judul', 'Judul tersimpan')
            ->assertSet('foto.0.fokus_x', 30)
            ->assertSet('foto.0.fokus_y', 40);
    }

    public function test_menghapus_thumbnail_ikut_menghapus_baris_fotonya(): void
    {
        $thumbnail = Thumbnail::create(['judul' => 'Akan dihapus']);
        $thumbnail->foto()->create(['path' => 'thumbnail/x.jpg', 'urutan' => 0]);

        $thumbnail->delete();

        $this->assertDatabaseCount('thumbnail_foto', 0);
    }
}
