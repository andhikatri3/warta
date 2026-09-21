<?php

namespace App\Livewire\Berita;

use App\Contracts\PenulisBerita;
use App\Enums\NadaBerita;
use App\Enums\PanjangBerita;
use App\Models\Berita;
use App\Services\Berita\BahanBerita;
use App\Services\Berita\GagalMenulis;
use App\Services\Berita\NaskahBerita;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tulis berita')]
class PembuatBerita extends Component
{
    public ?int $beritaId = null;

    // --- bahan 5W + 1H ---
    public string $apa = '';

    public string $siapa = '';

    public string $kapan = '';

    public string $diMana = '';

    public string $mengapa = '';

    public string $bagaimana = '';

    public string $kutipan = '';

    public string $penuturKutipan = '';

    public string $catatan = '';

    public string $nada = NadaBerita::Lugas->value;

    public string $panjang = PanjangBerita::Sedang->value;

    // --- naskah hasil ---
    /** @var array<int, string> */
    public array $judulPilihan = [];

    public string $judul = '';

    public string $lead = '';

    public string $isi = '';

    public string $meta = '';

    public string $slug = '';

    public bool $sudahMenulis = false;

    public function mount(?Berita $berita = null): void
    {
        $this->kapan = now()->translatedFormat('j F Y');

        if (! $berita?->exists) {
            return;
        }

        $bahan = $berita->bahan;

        $this->beritaId = $berita->id;
        $this->apa = $bahan['apa'] ?? '';
        $this->siapa = $bahan['siapa'] ?? '';
        $this->kapan = $bahan['kapan'] ?? '';
        $this->diMana = $bahan['di_mana'] ?? '';
        $this->mengapa = $bahan['mengapa'] ?? '';
        $this->bagaimana = $bahan['bagaimana'] ?? '';
        $this->kutipan = $bahan['kutipan'] ?? '';
        $this->penuturKutipan = $bahan['penutur_kutipan'] ?? '';
        $this->catatan = $bahan['catatan'] ?? '';
        $this->nada = $berita->nada->value;
        $this->panjang = $berita->panjang->value;

        $this->judul = $berita->judul;
        $this->judulPilihan = $berita->judul_alternatif ?: [$berita->judul];
        $this->lead = $berita->lead;
        $this->isi = $berita->isi;
        $this->meta = (string) $berita->meta;
        $this->slug = $berita->slug;
        $this->sudahMenulis = true;
    }

    protected function rules(): array
    {
        return [
            'apa' => ['required', 'string', 'min:10', 'max:500'],
            'siapa' => ['required', 'string', 'max:300'],
            'kapan' => ['required', 'string', 'max:120'],
            'diMana' => ['required', 'string', 'max:200'],
            'mengapa' => ['nullable', 'string', 'max:500'],
            'bagaimana' => ['nullable', 'string', 'max:800'],
            'kutipan' => ['nullable', 'string', 'max:800'],
            'penuturKutipan' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:800'],
            'nada' => ['required', 'string'],
            'panjang' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'apa.required' => 'Jelaskan dulu apa yang terjadi.',
            'apa.min' => 'Terlalu singkat. Model tidak boleh menebak sisanya, jadi tulis lebih lengkap.',
            'siapa.required' => 'Sebutkan siapa yang terlibat.',
            'kapan.required' => 'Sebutkan kapan peristiwanya.',
            'diMana.required' => 'Sebutkan di mana peristiwanya.',
        ];
    }

    public function tulis(PenulisBerita $penulis): void
    {
        $this->validate();

        try {
            $naskah = $penulis->tulis($this->bahan());
        } catch (GagalMenulis $e) {
            $this->addError('naskah', $e->getMessage());

            return;
        }

        $this->terapkan($naskah);
    }

    public function pilihJudul(int $indeks): void
    {
        if (isset($this->judulPilihan[$indeks])) {
            $this->judul = $this->judulPilihan[$indeks];
        }
    }

    public function simpan(): void
    {
        $this->validate();

        if (! $this->sudahMenulis || trim($this->judul) === '') {
            $this->addError('naskah', 'Belum ada naskah untuk disimpan.');

            return;
        }

        $berita = $this->beritaId ? Berita::findOrFail($this->beritaId) : new Berita;

        $berita->fill([
            'judul' => $this->judul,
            'slug' => $this->slug ?: str()->slug($this->judul),
            'lead' => $this->lead,
            'isi' => $this->isi,
            'meta' => $this->meta,
            'nada' => $this->nada,
            'panjang' => $this->panjang,
            'bahan' => $this->bahanSebagaiLarik(),
            'judul_alternatif' => $this->judulPilihan,
        ])->save();

        $this->beritaId = $berita->id;

        session()->flash('sukses', 'Berita tersimpan.');
    }

    private function terapkan(NaskahBerita $naskah): void
    {
        $this->judulPilihan = $naskah->judul;
        $this->judul = $naskah->judul[0];
        $this->lead = $naskah->lead;
        $this->isi = $naskah->isi;
        $this->meta = $naskah->meta;
        $this->slug = $naskah->slug;
        $this->sudahMenulis = true;

        $this->resetErrorBag('naskah');
    }

    private function bahan(): BahanBerita
    {
        return new BahanBerita(
            apa: $this->apa,
            siapa: $this->siapa,
            kapan: $this->kapan,
            diMana: $this->diMana,
            mengapa: $this->mengapa,
            bagaimana: $this->bagaimana,
            kutipan: $this->kutipan,
            penuturKutipan: $this->penuturKutipan,
            catatan: $this->catatan,
            nada: NadaBerita::tryFrom($this->nada) ?? NadaBerita::Lugas,
            panjang: PanjangBerita::tryFrom($this->panjang) ?? PanjangBerita::Sedang,
        );
    }

    private function bahanSebagaiLarik(): array
    {
        return [
            'apa' => $this->apa,
            'siapa' => $this->siapa,
            'kapan' => $this->kapan,
            'di_mana' => $this->diMana,
            'mengapa' => $this->mengapa,
            'bagaimana' => $this->bagaimana,
            'kutipan' => $this->kutipan,
            'penutur_kutipan' => $this->penuturKutipan,
            'catatan' => $this->catatan,
        ];
    }

    #[Computed]
    public function jumlahKata(): int
    {
        return str_word_count($this->lead."\n".$this->isi);
    }

    /** @return array<int, string> */
    #[Computed]
    public function paragraf(): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\n\s*\n/', $this->isi) ?: []),
            fn (string $p) => $p !== '',
        ));
    }

    public function render()
    {
        return view('livewire.berita.pembuat-berita');
    }
}
