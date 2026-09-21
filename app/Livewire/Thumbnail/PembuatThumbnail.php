<?php

namespace App\Livewire\Thumbnail;

use App\Enums\Aksen;
use App\Enums\ModelTeks;
use App\Enums\UkuranThumbnail;
use App\Models\Thumbnail;
use App\Services\Thumbnail\PenyimpanFoto;
use App\Support\TataLetakKolase;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Pembuat thumbnail')]
class PembuatThumbnail extends Component
{
    use WithFileUploads;

    public ?int $thumbnailId = null;

    public string $judul = '';

    public ?string $subjudul = null;

    public string $model = ModelTeks::Ceria->value;

    public string $aksen = Aksen::Biru->value;

    /**
     * Foto yang sedang disusun, sudah berada di disk dan punya URL sesama asal.
     *
     * Sengaja ditahan sebagai larik biasa, bukan koleksi Eloquent, karena
     * sebagian besar penyuntingan (geser urutan, geser titik fokus) tidak perlu
     * menyentuh basis data sama sekali — barisnya baru ditulis saat disimpan.
     *
     * @var array<int, array{path: string, url: string, fokus_x: int, fokus_y: int}>
     */
    public array $foto = [];

    public ?string $logoPath = null;

    public ?string $logoUrl = null;

    /** Unggahan sementara dari <input type="file"> foto. */
    public array $unggahan = [];

    /** Unggahan sementara dari <input type="file"> logo. */
    public $berkasLogo = null;

    /** Indeks foto yang panel titik fokusnya sedang terbuka. */
    public ?int $fokusAktif = null;

    public function mount(?Thumbnail $thumbnail = null): void
    {
        if (! $thumbnail?->exists) {
            // Datang dari halaman berita lewat tombol "Buat thumbnail": judulnya
            // dibawa di kueri supaya tidak perlu disalin tangan. Dipangkas ke
            // batas yang sama dengan aturan validasi agar judul panjang tidak
            // langsung menolak formulir yang belum disentuh pemakai.
            $this->judul = mb_substr(trim((string) request()->query('judul', '')), 0, 160);

            return;
        }

        $this->thumbnailId = $thumbnail->id;
        $this->judul = $thumbnail->judul;
        $this->subjudul = $thumbnail->subjudul;
        $this->model = $thumbnail->model_teks->value;
        $this->aksen = $thumbnail->aksen->value;
        $this->logoPath = $thumbnail->logo;
        $this->logoUrl = $thumbnail->logoUrl();

        $this->foto = $thumbnail->foto->map(fn ($f) => [
            'path' => $f->path,
            'url' => $f->url(),
            'fokus_x' => $f->fokus_x,
            'fokus_y' => $f->fokus_y,
        ])->all();
    }

    protected function rules(): array
    {
        return [
            'judul' => ['nullable', 'string', 'max:160'],
            'subjudul' => ['nullable', 'string', 'max:200'],
            'model' => ['required', 'string'],
            'aksen' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'judul.max' => 'Judul terlalu panjang, maksimal 160 karakter.',
            'unggahan.*.image' => 'Berkas :position bukan gambar.',
            'unggahan.*.mimes' => 'Gunakan JPG, PNG, atau WebP. Foto HEIC dari iPhone perlu dikonversi dulu.',
            'unggahan.*.max' => 'Ukuran berkas maksimal 12 MB.',
        ];
    }

    public function updatedUnggahan(): void
    {
        $this->validate([
            'unggahan.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:12288'],
        ]);

        $sisa = TataLetakKolase::MAKS_FOTO - count($this->foto);

        if ($sisa <= 0) {
            $this->unggahan = [];
            $this->addError('unggahan', 'Sudah ada '.TataLetakKolase::MAKS_FOTO.' foto. Hapus salah satu dulu.');

            return;
        }

        $diterima = array_slice($this->unggahan, 0, $sisa);
        $ditolak = count($this->unggahan) - count($diterima);

        $penyimpan = app(PenyimpanFoto::class);

        foreach ($diterima as $berkas) {
            $this->foto[] = [
                'path' => $penyimpan->simpanFoto($berkas),
                'url' => '',
                'fokus_x' => 50,
                'fokus_y' => 50,
            ];
        }

        // URL diisi setelah path final diketahui, dalam satu putaran, supaya
        // tidak ada foto yang sempat tampil dengan src kosong.
        foreach ($this->foto as $i => $f) {
            if ($f['url'] === '') {
                $this->foto[$i]['url'] = Storage::disk('public')->url($f['path']);
            }
        }

        $this->unggahan = [];

        if ($ditolak > 0) {
            $this->addError('unggahan', $ditolak.' foto tidak dipakai karena batasnya '.TataLetakKolase::MAKS_FOTO.' foto.');
        }
    }

    public function updatedBerkasLogo(): void
    {
        $this->validate([
            'berkasLogo' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ]);

        $this->logoPath = app(PenyimpanFoto::class)->simpanLogo($this->berkasLogo);
        $this->logoUrl = Storage::disk('public')->url($this->logoPath);
        $this->berkasLogo = null;
    }

    public function hapusLogo(): void
    {
        $this->logoPath = null;
        $this->logoUrl = null;
    }

    public function hapusFoto(int $indeks): void
    {
        if (! isset($this->foto[$indeks])) {
            return;
        }

        // Berkasnya tidak ikut dihapus di sini. Kalau thumbnail ini sudah
        // tersimpan dan penyuntingan batal di tengah jalan, baris di basis data
        // masih menunjuk ke berkas itu. Yang benar-benar lepas dibereskan saat
        // disimpan, dan sisa draf yang telantar oleh perintah warta:bersihkan-foto.
        unset($this->foto[$indeks]);
        $this->foto = array_values($this->foto);
        $this->fokusAktif = null;
    }

    public function geserFoto(int $dari, int $ke): void
    {
        if (! isset($this->foto[$dari], $this->foto[$ke])) {
            return;
        }

        $dipindah = array_splice($this->foto, $dari, 1);
        array_splice($this->foto, $ke, 0, $dipindah);

        $this->fokusAktif = null;
    }

    public function aturFokus(int $indeks, float $x, float $y): void
    {
        if (! isset($this->foto[$indeks])) {
            return;
        }

        $this->foto[$indeks]['fokus_x'] = (int) round(max(0, min(100, $x)));
        $this->foto[$indeks]['fokus_y'] = (int) round(max(0, min(100, $y)));
    }

    public function bukaFokus(int $indeks): void
    {
        $this->fokusAktif = $this->fokusAktif === $indeks ? null : $indeks;
    }

    public function simpan(): void
    {
        $this->validate();

        $thumbnail = $this->thumbnailId
            ? Thumbnail::findOrFail($this->thumbnailId)
            : new Thumbnail;

        $thumbnail->fill([
            'judul' => $this->judul,
            'subjudul' => $this->subjudul,
            'model_teks' => $this->model,
            'aksen' => $this->aksen,
            'logo' => $this->logoPath,
        ])->save();

        $dipakai = collect($this->foto)->pluck('path');

        // Foto yang dicabut dari susunan baru benar-benar dibuang di sini,
        // sesudah penyimpanan berhasil.
        $dibuang = $thumbnail->foto()->whereNotIn('path', $dipakai)->get();
        if ($dibuang->isNotEmpty()) {
            Storage::disk('public')->delete($dibuang->pluck('path')->all());
            $thumbnail->foto()->whereKey($dibuang->pluck('id'))->delete();
        }

        foreach ($this->foto as $urutan => $f) {
            $thumbnail->foto()->updateOrCreate(
                ['path' => $f['path']],
                ['urutan' => $urutan, 'fokus_x' => $f['fokus_x'], 'fokus_y' => $f['fokus_y']],
            );
        }

        $this->thumbnailId = $thumbnail->id;

        session()->flash('sukses', 'Thumbnail tersimpan.');
    }

    #[Computed]
    public function modelTerpilih(): ModelTeks
    {
        return ModelTeks::tryFrom($this->model) ?? ModelTeks::Ceria;
    }

    #[Computed]
    public function aksenTerpilih(): Aksen
    {
        return Aksen::tryFrom($this->aksen) ?? Aksen::Biru;
    }

    #[Computed]
    public function ukuranSemua(): array
    {
        return UkuranThumbnail::semua();
    }

    public function render()
    {
        return view('livewire.thumbnail.pembuat-thumbnail');
    }
}
