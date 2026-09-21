<?php

namespace App\Models;

use App\Enums\Aksen;
use App\Enums\GayaThumbnail;
use App\Enums\ModelTeks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Thumbnail extends Model
{
    protected $table = 'thumbnail';

    protected $fillable = [
        'judul',
        'subjudul',
        'gaya',
        'model_teks',
        'aksen',
        'logo',
    ];

    protected function casts(): array
    {
        return [
            'gaya' => GayaThumbnail::class,
            'model_teks' => ModelTeks::class,
            'aksen' => Aksen::class,
        ];
    }

    protected static function booted(): void
    {
        // Berkas gambar tidak ikut terhapus oleh kunci asing, jadi dibersihkan
        // di sini. Tanpa ini folder storage terus tumbuh oleh foto milik
        // thumbnail yang sudah lama dibuang.
        static::deleting(function (self $thumbnail) {
            $jalur = $thumbnail->foto->pluck('path')
                ->push($thumbnail->logo)
                ->filter()
                ->all();

            if ($jalur !== []) {
                Storage::disk('public')->delete($jalur);
            }
        });
    }

    public function foto(): HasMany
    {
        return $this->hasMany(ThumbnailFoto::class)->orderBy('urutan');
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    /**
     * Nama dasar untuk berkas unduhan. Judul berita dipakai apa adanya supaya
     * berkas yang mendarat di folder Unduhan masih bisa dikenali, tapi
     * dipangkas agar tidak melewati batas nama berkas Windows.
     */
    public function namaBerkas(): string
    {
        return mb_substr(str()->slug($this->judul) ?: 'thumbnail', 0, 60);
    }
}
