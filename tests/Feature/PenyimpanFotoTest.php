<?php

namespace Tests\Feature;

use App\Livewire\Thumbnail\PembuatThumbnail;
use App\Services\Thumbnail\PenyimpanFoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Menjalankan pemroses foto dengan berkas gambar sungguhan.
 *
 * Tes ini ada karena pustaka pengolah gambar sempat dipanggil dengan nama
 * metode dari versi yang berbeda, dan tidak ada satu pun tes yang benar-benar
 * mengunggah sesuatu — jadi seluruh rangkaian tes lolos sementara tombol
 * "Tambah foto" gagal total di peramban. Apa pun yang menyentuh pustaka itu
 * harus dilalui oleh tes, bukan sekadar dibaca.
 */
class PenyimpanFotoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function penyimpan(): PenyimpanFoto
    {
        return app(PenyimpanFoto::class);
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function periksaGambar(string $path): array
    {
        $isi = Storage::disk('public')->get($path);
        $info = getimagesizefromstring($isi);

        $this->assertNotFalse($info, "Berkas {$path} bukan gambar yang sah");

        return $info;
    }

    public function test_foto_tersimpan_sebagai_jpeg_yang_sah(): void
    {
        $path = $this->penyimpan()->simpanFoto(
            UploadedFile::fake()->image('liputan.jpg', 1400, 900),
        );

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.jpg', $path);

        [$lebar, $tinggi, $jenis] = $this->periksaGambar($path);

        $this->assertSame(IMAGETYPE_JPEG, $jenis);
        $this->assertSame(1400, $lebar);
        $this->assertSame(900, $tinggi);
    }

    public function test_foto_raksasa_dikecilkan_sampai_batas_sisi(): void
    {
        $path = $this->penyimpan()->simpanFoto(
            UploadedFile::fake()->image('besar.jpg', 4000, 3000),
        );

        [$lebar, $tinggi] = $this->periksaGambar($path);

        $this->assertSame(PenyimpanFoto::SISI_MAKS, $lebar);
        $this->assertSame(1500, $tinggi, 'Rasio aslinya harus tetap terjaga');
    }

    public function test_foto_yang_sudah_kecil_tidak_ikut_dibesarkan(): void
    {
        $path = $this->penyimpan()->simpanFoto(
            UploadedFile::fake()->image('kecil.jpg', 640, 480),
        );

        [$lebar, $tinggi] = $this->periksaGambar($path);

        $this->assertSame(640, $lebar);
        $this->assertSame(480, $tinggi);
    }

    public function test_logo_tersimpan_sebagai_png(): void
    {
        $path = $this->penyimpan()->simpanLogo(
            UploadedFile::fake()->image('logo.png', 800, 300),
        );

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.png', $path);

        [$lebar, , $jenis] = $this->periksaGambar($path);

        $this->assertSame(IMAGETYPE_PNG, $jenis);
        $this->assertSame(PenyimpanFoto::SISI_LOGO_MAKS, $lebar);
    }

    public function test_mengunggah_lewat_komponen_menambah_foto_ke_susunan(): void
    {
        Livewire::test(PembuatThumbnail::class)
            ->set('unggahan', [
                UploadedFile::fake()->image('satu.jpg', 1200, 800),
                UploadedFile::fake()->image('dua.jpg', 1200, 800),
            ])
            ->assertHasNoErrors()
            ->assertCount('foto', 2)
            ->assertSet('foto.0.fokus_x', 50)
            ->assertSet('foto.1.fokus_y', 50);
    }

    public function test_unggahan_melebihi_batas_ditolak_dengan_pesan(): void
    {
        $komponen = Livewire::test(PembuatThumbnail::class)
            ->set('unggahan', collect(range(1, 6))
                ->map(fn (int $i) => UploadedFile::fake()->image("f{$i}.jpg", 800, 600))
                ->all());

        $komponen->assertCount('foto', 4)->assertHasErrors('unggahan');
    }

    public function test_url_foto_bersifat_relatif_agar_kanvas_tidak_ternajis(): void
    {
        $komponen = Livewire::test(PembuatThumbnail::class)
            ->set('unggahan', [UploadedFile::fake()->image('satu.jpg', 900, 600)]);

        $url = $komponen->get('foto')[0]['url'];

        // URL absolut lintas asal membuat kanvas ternajis dan seluruh
        // penangkapan gambar gagal, jadi ini bagian dari perilaku, bukan detail.
        $this->assertStringStartsWith('/storage/', $url);
    }
}
