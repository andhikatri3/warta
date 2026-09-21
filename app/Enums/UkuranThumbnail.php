<?php

namespace App\Enums;

/**
 * Ukuran keluaran thumbnail.
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
     * Pemangkas jumlah baris judul agar tidak meluber dari kanvas.
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
     * Subjudul dipangkas lebih ketat daripada judul: ia yang mengalah kalau
     * ruang habis, karena judul yang terpenggal jauh lebih merusak daripada
     * subjudul yang terpenggal.
     */
    public function klemSubjudul(): string
    {
        return match ($this) {
            self::Facebook, self::Persegi => 'line-clamp-1',
            self::Story => 'line-clamp-2',
        };
    }

    /**
     * Bentuk kanvas. Inilah yang menentukan arah susunan foto — lihat
     * TataLetakKolase.
     *
     * Dihitung dari kanvas penuh karena kolase mengisi seluruhnya: judul
     * ditumpuk di atas foto, tidak lagi memotong ruang untuk pita sendiri.
     */
    public function bentuk(): BentukKolase
    {
        $rasio = $this->lebar() / $this->tinggi();

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
