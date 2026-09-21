<?php

namespace App\Enums;

/**
 * Ukuran keluaran thumbnail.
 *
 * Tiap ukuran membawa tinggi pita teksnya sendiri, bukan satu nilai yang
 * dibagi bersama, karena proporsi pita yang enak dilihat di tautan Facebook
 * yang lebar terlihat terlalu tipis saat dipakai di Story yang jangkung.
 *
 * `basis` adalah font-size yang dipasang pada elemen kanvas. Seluruh ukuran
 * teks, jarak, dan padding di dalam kanvas ditulis dalam satuan em terhadap
 * nilai ini, jadi satu templat Blade bisa melayani ketiga ukuran tanpa
 * percabangan: ganti basisnya, seluruh isinya ikut menyesuaikan.
 */
enum UkuranThumbnail: string
{
    case Facebook = 'facebook';
    case Persegi = 'persegi';
    case Story = 'story';

    public function lebar(): int
    {
        return match ($this) {
            self::Facebook => 1200,
            self::Persegi, self::Story => 1080,
        };
    }

    public function tinggi(): int
    {
        return match ($this) {
            self::Facebook => 630,
            self::Persegi => 1080,
            self::Story => 1920,
        };
    }

    /**
     * Tinggi pita teks.
     *
     * Angkanya bukan selera, melainkan hasil menjumlahkan isi terburuk yang
     * masih boleh muat: baris label, judul sepenuh batas barisnya, subjudul,
     * jarak antarunsur, dan padding atas-bawah. Nilai Facebook yang pertama
     * dipakai (172) ternyata kurang belasan piksel, dan kekurangan itu tidak
     * terlihat di pratinjau kecil — yang tampak hanya subjudul yang hilang
     * separuh di tepi bawah berkas jadinya.
     */
    public function tinggiPita(): int
    {
        return match ($this) {
            self::Facebook, self::Persegi => 210,
            self::Story => 330,
        };
    }

    public function basis(): int
    {
        return match ($this) {
            self::Facebook => 60,
            self::Persegi => 58,
            self::Story => 66,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Persegi => 'Persegi',
            self::Story => 'Story',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Facebook => 'Pratinjau tautan Facebook',
            self::Persegi => 'Kiriman feed Facebook & Instagram',
            self::Story => 'Story, Reels, dan WhatsApp',
        };
    }

    public function rasio(): string
    {
        return match ($this) {
            self::Facebook => '1.91:1',
            self::Persegi => '1:1',
            self::Story => '9:16',
        };
    }

    /**
     * Pemangkas jumlah baris judul agar tidak meluber dari pita.
     *
     * Luapan di kanvas berukuran tetap tidak memunculkan bilah gulir — ia
     * hanya terpotong diam-diam di tempat yang tidak terduga saat dijadikan
     * gambar, jadi lebih baik dipangkas dengan sengaja.
     *
     * Dikembalikan sebagai nama kelas utuh, bukan angka yang nanti
     * disambung di Blade, karena pemindai Tailwind membaca berkas sebagai
     * teks: 'line-clamp-'.$n tidak pernah terlihat olehnya, dan kelasnya
     * diam-diam tidak ikut terbangun.
     */
    public function klemJudul(): string
    {
        return match ($this) {
            self::Facebook, self::Persegi => 'line-clamp-2',
            self::Story => 'line-clamp-3',
        };
    }

    /**
     * Subjudul dipangkas lebih ketat daripada judul, dan paling ketat di
     * kanvas yang pitanya paling sempit: ia yang mengalah kalau ruang habis,
     * karena judul yang terpenggal jauh lebih merusak daripada subjudul yang
     * terpenggal.
     */
    public function klemSubjudul(): string
    {
        return match ($this) {
            self::Facebook, self::Persegi => 'line-clamp-1',
            self::Story => 'line-clamp-2',
        };
    }

    /**
     * Bentuk area kolase setelah pita teks dipotong. Inilah yang menentukan
     * arah susunan foto — lihat TataLetakKolase.
     */
    public function bentuk(): BentukKolase
    {
        $rasio = $this->lebar() / ($this->tinggi() - $this->tinggiPita());

        return match (true) {
            $rasio >= 1.3 => BentukKolase::Lebar,
            $rasio >= 0.8 => BentukKolase::Kotak,
            default => BentukKolase::Tinggi,
        };
    }

    public function berkas(string $dasar): string
    {
        return $dasar.'-'.$this->value.'-'.$this->lebar().'x'.$this->tinggi().'.png';
    }

    /** @return array<int, self> */
    public static function semua(): array
    {
        return self::cases();
    }
}
