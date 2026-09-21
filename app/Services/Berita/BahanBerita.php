<?php

namespace App\Services\Berita;

use App\Enums\NadaBerita;
use App\Enums\PanjangBerita;

/**
 * Bahan mentah sebuah berita: 5W + 1H, ditambah kutipan narasumber dan
 * pilihan gaya.
 *
 * Seluruh fakta dalam berita jadi berasal dari sini. Model tidak diminta
 * mencari tahu apa pun — ia hanya menyusun ulang isi objek ini menjadi prosa
 * jurnalistik. Itu pembagian tugas yang disengaja: penyusunnya yang
 * bertanggung jawab atas kebenaran fakta, model atas bentuk kalimatnya.
 */
class BahanBerita
{
    public function __construct(
        public string $apa,
        public string $siapa,
        public string $kapan,
        public string $diMana,
        public string $mengapa = '',
        public string $bagaimana = '',
        public string $kutipan = '',
        public string $penuturKutipan = '',
        public string $catatan = '',
        public NadaBerita $nada = NadaBerita::Lugas,
        public PanjangBerita $panjang = PanjangBerita::Sedang,
    ) {}

    /**
     * Menyusun bahan menjadi daftar berlabel untuk dikirim ke model.
     *
     * Ruas kosong sengaja dibuang, tidak dikirim sebagai label tanpa isi.
     * Label kosong mengundang model mengisinya sendiri; ruas yang memang tidak
     * ada lebih baik tidak pernah disebut.
     */
    public function sebagaiDaftar(): string
    {
        $ruas = [
            'Apa yang terjadi' => $this->apa,
            'Siapa yang terlibat' => $this->siapa,
            'Kapan' => $this->kapan,
            'Di mana' => $this->diMana,
            'Mengapa' => $this->mengapa,
            'Bagaimana jalannya' => $this->bagaimana,
            'Catatan tambahan' => $this->catatan,
        ];

        $baris = [];

        foreach ($ruas as $label => $isi) {
            if (trim($isi) !== '') {
                $baris[] = "{$label}: ".trim($isi);
            }
        }

        return implode("\n", $baris);
    }

    public function punyaKutipan(): bool
    {
        return trim($this->kutipan) !== '';
    }
}
