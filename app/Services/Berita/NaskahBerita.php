<?php

namespace App\Services\Berita;

/**
 * Naskah jadi: beberapa alternatif judul, lead, badan berita, dan ruas meta.
 *
 * Judulnya sengaja jamak. Judul adalah bagian yang paling sering ditolak dan
 * paling murah dibuat ulang; memberi tiga pilihan sekaligus jauh lebih hemat
 * daripada menyuruh menulis ulang seluruh berita hanya karena judulnya kurang
 * pas.
 */
class NaskahBerita
{
    /**
     * @param  array<int, string>  $judul
     */
    public function __construct(
        public array $judul,
        public string $lead,
        public string $isi,
        public string $meta,
        public string $slug,
    ) {}

    public static function dariLarik(array $data): self
    {
        $judul = array_values(array_filter(
            array_map(trim(...), (array) ($data['judul'] ?? [])),
            fn (string $j) => $j !== '',
        ));

        if ($judul === []) {
            throw new GagalMenulis('Model tidak mengembalikan satu pun judul.');
        }

        return new self(
            judul: $judul,
            lead: trim((string) ($data['lead'] ?? '')),
            isi: trim((string) ($data['isi'] ?? '')),
            meta: trim((string) ($data['meta'] ?? '')),
            slug: trim((string) ($data['slug'] ?? '')) ?: str()->slug($judul[0]),
        );
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
        return str_word_count(strip_tags($this->lead."\n".$this->isi));
    }

    public function teksLengkap(): string
    {
        return trim($this->lead."\n\n".$this->isi);
    }
}
