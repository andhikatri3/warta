<?php

namespace Tests\Feature;

use App\Contracts\PenulisBerita;
use App\Enums\NadaBerita;
use App\Enums\PanjangBerita;
use App\Services\Berita\BahanBerita;
use App\Services\Berita\GagalMenulis;
use App\Services\Berita\PenulisClaude;
use App\Services\Berita\PenulisGemini;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Menguji penulis Gemini tanpa menyentuh layanan sungguhan.
 *
 * Inilah keuntungan memakai klien HTTP Laravel alih-alih SDK: bentuk
 * permintaan yang dikirim dan cara balasan dibaca keduanya bisa diperiksa.
 * Kesalahan seperti salah nama ruas atau salah jalur pembacaan — jenis yang
 * biasanya baru ketahuan saat ada pemakai menekan tombolnya — tertangkap di
 * sini.
 */
class PenulisGeminiTest extends TestCase
{
    private const ALAMAT = 'generativelanguage.googleapis.com/*';

    protected function setUp(): void
    {
        parent::setUp();

        config(['gemini.api_key' => 'kunci-uji', 'gemini.model' => 'gemini-2.5-flash']);
    }

    private function bahan(): BahanBerita
    {
        return new BahanBerita(
            apa: 'Pemerintah desa meresmikan gedung Posyandu baru',
            siapa: 'Kepala Desa Sutrisno dan sekitar 80 warga',
            kapan: 'Sabtu, 21 September 2026',
            diMana: 'Balai Dusun Krajan',
            kutipan: 'Gedung ini milik warga.',
            penuturKutipan: 'Kepala Desa Sutrisno',
            nada: NadaBerita::Resmi,
            panjang: PanjangBerita::Panjang,
        );
    }

    /** Balasan sungguhan Interactions API: teksnya di dalam larik outputs. */
    private function balasanSukses(array $naskah = []): array
    {
        $naskah = $naskah ?: [
            'judul' => ['Judul satu', 'Judul dua', 'Judul tiga'],
            'lead' => 'Lead beritanya.',
            'isi' => "Paragraf pertama.\n\nParagraf kedua.",
            'meta' => 'Ringkasan.',
            'slug' => 'judul-satu',
        ];

        return [
            'id' => 'int_123',
            'role' => 'model',
            'outputs' => [
                ['type' => 'text', 'text' => json_encode($naskah)],
            ],
        ];
    }

    public function test_naskah_dibaca_dari_larik_outputs(): void
    {
        Http::fake([self::ALAMAT => Http::response($this->balasanSukses())]);

        $naskah = (new PenulisGemini)->tulis($this->bahan());

        $this->assertCount(3, $naskah->judul);
        $this->assertSame('Judul satu', $naskah->judul[0]);
        $this->assertSame('Lead beritanya.', $naskah->lead);
        $this->assertCount(2, $naskah->paragraf());
    }

    public function test_teks_yang_terpecah_beberapa_blok_disambung(): void
    {
        // Balasan boleh terpecah ke beberapa blok teks berurutan; kalau hanya
        // blok pertama yang dibaca, JSON-nya tidak utuh dan gagal diurai.
        $naskah = json_encode([
            'judul' => ['A', 'B', 'C'],
            'lead' => 'Lead.',
            'isi' => 'Isi.',
            'meta' => 'Meta.',
            'slug' => 'a',
        ]);

        Http::fake([self::ALAMAT => Http::response([
            'outputs' => [
                ['type' => 'text', 'text' => substr($naskah, 0, 20)],
                ['type' => 'text', 'text' => substr($naskah, 20)],
            ],
        ])]);

        $this->assertSame('Lead.', (new PenulisGemini)->tulis($this->bahan())->lead);
    }

    public function test_blok_selain_teks_diabaikan(): void
    {
        Http::fake([self::ALAMAT => Http::response([
            'outputs' => [
                ['type' => 'thought', 'text' => 'ini bukan naskah'],
                ['type' => 'text', 'text' => json_encode([
                    'judul' => ['A'], 'lead' => 'L', 'isi' => 'I', 'meta' => 'M', 'slug' => 's',
                ])],
            ],
        ])]);

        $this->assertSame('L', (new PenulisGemini)->tulis($this->bahan())->lead);
    }

    public function test_permintaan_memakai_endpoint_header_dan_ruas_yang_benar(): void
    {
        Http::fake([self::ALAMAT => Http::response($this->balasanSukses())]);

        (new PenulisGemini)->tulis($this->bahan());

        Http::assertSent(function (Request $r) {
            $isi = $r->data();

            return $r->url() === 'https://generativelanguage.googleapis.com/v1beta/interactions'
                && $r->hasHeader('x-goog-api-key', 'kunci-uji')
                && $r->hasHeader('Api-Revision')
                && $isi['model'] === 'gemini-2.5-flash'
                && str_contains($isi['system_instruction'], 'Pakai hanya fakta yang ada di bahan')
                && $isi['response_format']['mime_type'] === 'application/json'
                && $isi['response_format']['schema']['properties']['judul']['type'] === 'array';
        });
    }

    public function test_seluruh_bahan_ikut_terkirim_ke_model(): void
    {
        Http::fake([self::ALAMAT => Http::response($this->balasanSukses())]);

        (new PenulisGemini)->tulis($this->bahan());

        Http::assertSent(function (Request $r) {
            $masukan = $r->data()['input'];

            return str_contains($masukan, 'Balai Dusun Krajan')
                && str_contains($masukan, 'Kepala Desa Sutrisno')
                && str_contains($masukan, 'Gedung ini milik warga.')
                && str_contains($masukan, 'salin persis apa adanya');
        });
    }

    public function test_skema_tidak_memakai_kata_kunci_yang_ditolak_gemini(): void
    {
        Http::fake([self::ALAMAT => Http::response($this->balasanSukses())]);

        (new PenulisGemini)->tulis($this->bahan());

        Http::assertSent(function (Request $r) {
            $skema = json_encode($r->data()['response_format']['schema']);

            // Kata kunci yang tidak dikenal ditolak sebagai galat 400,
            // bukan diabaikan diam-diam.
            return ! str_contains($skema, 'additionalProperties')
                && ! str_contains($skema, 'minItems');
        });
    }

    public function test_kunci_kosong_ditolak_sebelum_permintaan_dikirim(): void
    {
        config(['gemini.api_key' => '']);
        Http::fake();

        try {
            (new PenulisGemini)->tulis($this->bahan());
            $this->fail('Seharusnya melempar GagalMenulis');
        } catch (GagalMenulis $e) {
            $this->assertStringContainsString('GEMINI_API_KEY', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_kuota_habis_dijelaskan_sebagai_kuota_bukan_gagal_biasa(): void
    {
        Http::fake([self::ALAMAT => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);

        $this->expectException(GagalMenulis::class);
        $this->expectExceptionMessageMatches('/[Kk]uota/');

        (new PenulisGemini)->tulis($this->bahan());
    }

    public function test_kunci_ditolak_menghasilkan_pesan_tentang_kunci(): void
    {
        Http::fake([self::ALAMAT => Http::response(['error' => ['message' => 'API key not valid']], 403)]);

        $this->expectException(GagalMenulis::class);
        $this->expectExceptionMessageMatches('/[Kk]unci API Gemini ditolak/');

        (new PenulisGemini)->tulis($this->bahan());
    }

    public function test_pesan_galat_dari_layanan_ikut_ditampilkan(): void
    {
        Http::fake([self::ALAMAT => Http::response(
            ['error' => ['message' => 'Unknown name "schemaa"']], 400
        )]);

        $this->expectException(GagalMenulis::class);
        $this->expectExceptionMessageMatches('/schemaa/');

        (new PenulisGemini)->tulis($this->bahan());
    }

    public function test_balasan_yang_bukan_json_tidak_menjadi_layar_galat(): void
    {
        Http::fake([self::ALAMAT => Http::response([
            'outputs' => [['type' => 'text', 'text' => 'maaf, saya tidak bisa']],
        ])]);

        $this->expectException(GagalMenulis::class);
        $this->expectExceptionMessageMatches('/tidak berbentuk naskah/');

        (new PenulisGemini)->tulis($this->bahan());
    }

    public function test_konfigurasi_menentukan_penulis_yang_dipakai(): void
    {
        config(['penulis.driver' => 'gemini']);
        $this->assertInstanceOf(PenulisGemini::class, $this->app->make(PenulisBerita::class));

        $this->app->forgetInstance(PenulisBerita::class);

        config(['penulis.driver' => 'claude']);
        $this->assertInstanceOf(PenulisClaude::class, $this->app->make(PenulisBerita::class));
    }

    public function test_penulis_yang_tidak_dikenal_gagal_dengan_jelas(): void
    {
        config(['penulis.driver' => 'entah-apa']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/entah-apa/');

        $this->app->make(PenulisBerita::class);
    }
}
