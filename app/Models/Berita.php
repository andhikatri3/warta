<?php

namespace App\Models;

use App\Enums\NadaBerita;
use App\Enums\PanjangBerita;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'judul',
        'slug',
        'lead',
        'isi',
        'meta',
        'nada',
        'panjang',
        'bahan',
        'judul_alternatif',
    ];

    protected function casts(): array
    {
        return [
            'bahan' => 'array',
            'judul_alternatif' => 'array',
            'nada' => NadaBerita::class,
            'panjang' => PanjangBerita::class,
        ];
    }

    /** @return array<int, string> */
    public function paragraf(): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\n\s*\n/', $this->isi) ?: []),
            fn (string $p) => $p !== '',
        ));
    }

    public function jumlahKata(): int
    {
        return str_word_count($this->lead."\n".$this->isi);
    }
}
