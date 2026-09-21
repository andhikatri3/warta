<?php

namespace Tests\Feature;

use App\Contracts\PenulisBerita;
use App\Enums\NadaBerita;
use App\Enums\PanjangBerita;
use App\Livewire\Berita\PembuatBerita;
use App\Models\Berita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Dukungan\PenulisTiruan;
use Tests\TestCase;

class PembuatBeritaTest extends TestCase
{
    use RefreshDatabase;

    private function pasangTiruan(?PenulisTiruan $tiruan = null): PenulisTiruan
    {
        $tiruan ??= new PenulisTiruan;

        $this->app->instance(PenulisBerita::class, $tiruan);

        return $tiruan;
    }

    private function isiBahan(\Livewire\Features\SupportTesting\Testable $k): \Livewire\Features\SupportTesting\Testable
    {
        return $k
            ->set('apa', 'Pemerintah desa meresmikan gedung Posyandu baru di Dusun Krajan')
            ->set('siapa', 'Kepala Desa Sutrisno, Camat Nguling, dan sekitar 80 warga')
            ->set('kapan', 'Sabtu, 21 September 2026')
            ->set('diMana', 'Balai Dusun Krajan, Desa Penunggul');
    }

    public function test_halaman_penulis_terbuka(): void
    {
        $this->get(route('berita.baru'))->assertOk();
    }

    public function test_halaman_arsip_terbuka(): void
    {
        $this->get(route('berita.riwayat'))->assertOk();
    }

    public function test_bahan_wajib_diisi_sebelum_model_dipanggil(): void
    {
        $tiruan = $this->pasangTiruan();

        Livewire::test(PembuatBerita::class)
            ->set('apa', '')
            ->call('tulis')
            ->assertHasErrors(['apa' => 'required']);

        $this->assertSame(0, $tiruan->jumlahPanggilan, 'Model tidak boleh dipanggil saat bahannya belum sah');
    }

    public function test_bahan_terlalu_singkat_ditolak(): void
    {
        $tiruan = $this->pasangTiruan();

        Livewire::test(PembuatBerita::class)
            ->set('apa', 'Rapat')
            ->set('siapa', 'Warga')
            ->set('kapan', 'Kemarin')
            ->set('diMana', 'Balai desa')
            ->call('tulis')
            ->assertHasErrors(['apa' => 'min']);

        $this->assertSame(0, $tiruan->jumlahPanggilan);
    }

    public function test_menulis_mengisi_naskah_dan_tiga_pilihan_judul(): void
    {
        $this->pasangTiruan();

        $komponen = $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->call('tulis')
            ->assertHasNoErrors()
            ->assertSet('sudahMenulis', true)
            ->assertCount('judulPilihan', 3);

        $this->assertSame('Posyandu baru diresmikan di Desa Penunggul', $komponen->get('judul'));
        $this->assertNotSame('', $komponen->get('lead'));
        $this->assertNotSame('', $komponen->get('slug'));
    }

    public function test_seluruh_ruas_bahan_sampai_ke_penulis(): void
    {
        $tiruan = $this->pasangTiruan();

        $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->set('mengapa', 'Gedung lama sudah tidak muat')
            ->set('bagaimana', 'Sambutan, pemotongan pita, lalu pemeriksaan balita')
            ->set('kutipan', 'Gedung ini milik warga.')
            ->set('penuturKutipan', 'Kepala Desa Sutrisno')
            ->set('nada', NadaBerita::Resmi->value)
            ->set('panjang', PanjangBerita::Panjang->value)
            ->call('tulis');

        $bahan = $tiruan->bahanTerakhir;

        $this->assertNotNull($bahan);
        $this->assertSame('Gedung lama sudah tidak muat', $bahan->mengapa);
        $this->assertSame('Kepala Desa Sutrisno', $bahan->penuturKutipan);
        $this->assertSame(NadaBerita::Resmi, $bahan->nada);
        $this->assertSame(PanjangBerita::Panjang, $bahan->panjang);
        $this->assertTrue($bahan->punyaKutipan());
    }

    public function test_ruas_kosong_tidak_dikirim_sebagai_label_kosong(): void
    {
        $tiruan = $this->pasangTiruan();

        $this->isiBahan(Livewire::test(PembuatBerita::class))->call('tulis');

        $daftar = $tiruan->bahanTerakhir->sebagaiDaftar();

        // Label tanpa isi mengundang model mengisinya sendiri.
        $this->assertStringNotContainsString('Mengapa:', $daftar);
        $this->assertStringNotContainsString('Bagaimana jalannya:', $daftar);
        $this->assertStringContainsString('Apa yang terjadi:', $daftar);
    }

    public function test_kegagalan_penulis_muncul_sebagai_pesan_bukan_layar_galat(): void
    {
        $this->pasangTiruan(PenulisTiruan::yangGagal('Kunci API Anthropic belum diisi.'));

        $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->call('tulis')
            ->assertHasErrors('naskah')
            ->assertSet('sudahMenulis', false);
    }

    public function test_judul_bisa_diganti_ke_alternatif_lain(): void
    {
        $this->pasangTiruan();

        $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->call('tulis')
            ->call('pilihJudul', 2)
            ->assertSet('judul', 'Gedung Posyandu Penunggul mulai melayani warga');
    }

    public function test_menyimpan_menulis_naskah_beserta_bahannya(): void
    {
        $this->pasangTiruan();

        $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->set('mengapa', 'Gedung lama sudah tidak muat')
            ->call('tulis')
            ->call('simpan')
            ->assertHasNoErrors();

        $berita = Berita::firstOrFail();

        $this->assertSame('Posyandu baru diresmikan di Desa Penunggul', $berita->judul);
        $this->assertCount(3, $berita->judul_alternatif);

        // Bahannya ikut tersimpan supaya asal tiap kalimat bisa ditelusuri
        // dan naskahnya bisa ditulis ulang tanpa mengetik fakta dari awal.
        $this->assertSame('Gedung lama sudah tidak muat', $berita->bahan['mengapa']);
        $this->assertSame('Balai Dusun Krajan, Desa Penunggul', $berita->bahan['di_mana']);
    }

    public function test_menyimpan_tanpa_naskah_ditolak(): void
    {
        $this->pasangTiruan();

        $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->call('simpan')
            ->assertHasErrors('naskah');

        $this->assertSame(0, Berita::count());
    }

    public function test_menyimpan_dua_kali_memperbarui_baris_yang_sama(): void
    {
        $this->pasangTiruan();

        $komponen = $this->isiBahan(Livewire::test(PembuatBerita::class))
            ->call('tulis')
            ->call('simpan');

        $komponen->set('judul', 'Judul suntingan')->call('simpan');

        $this->assertSame(1, Berita::count());
        $this->assertSame('Judul suntingan', Berita::first()->judul);
    }

    public function test_membuka_berita_tersimpan_memuat_bahan_dan_naskahnya(): void
    {
        $this->pasangTiruan();

        $berita = Berita::create([
            'judul' => 'Judul tersimpan',
            'slug' => 'judul-tersimpan',
            'lead' => 'Lead tersimpan.',
            'isi' => 'Paragraf pertama.',
            'meta' => 'Meta tersimpan.',
            'nada' => NadaBerita::Hangat->value,
            'panjang' => PanjangBerita::Pendek->value,
            'bahan' => ['apa' => 'Sesuatu terjadi', 'di_mana' => 'Balai desa'],
            'judul_alternatif' => ['Judul tersimpan', 'Alternatif kedua'],
        ]);

        Livewire::test(PembuatBerita::class, ['berita' => $berita])
            ->assertSet('judul', 'Judul tersimpan')
            ->assertSet('apa', 'Sesuatu terjadi')
            ->assertSet('diMana', 'Balai desa')
            ->assertSet('nada', 'hangat')
            ->assertSet('sudahMenulis', true);
    }

    public function test_judul_berita_terbawa_ke_pembuat_thumbnail(): void
    {
        $this->get(route('thumbnail.baru', ['judul' => 'Posyandu baru diresmikan']))
            ->assertOk()
            ->assertSee('Posyandu baru diresmikan', escape: false);
    }
}
