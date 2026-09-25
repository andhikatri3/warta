<?php

namespace App\Support;

/**
 * Menyusun baris akun resmi di kanvas dari nilai bawaan config/warta.php dan
 * ubahan per thumbnail.
 *
 * Ubahan disimpan sebagai peta ikon => teks akun, bukan larik lengkap.
 * Daftar ikon dan urutannya selalu datang dari konfigurasi: nama ikon dipakai
 * sebagai nama komponen Blade (<x-dynamic-component>), jadi ia tidak boleh
 * pernah berasal dari masukan pemakai. Yang bisa diubah dari formulir hanya
 * teksnya.
 */
class AkunSosmed
{
    /** @return array<string, string> ikon => teks akun bawaan */
    public static function bawaan(): array
    {
        return collect(config('warta.sosmed', []))
            ->mapWithKeys(fn (array $s) => [$s['ikon'] => (string) $s['akun']])
            ->all();
    }

    /**
     * @param  array<string, string|null>|null  $ubahan
     * @return array<int, array{ikon: string, akun: string}>
     */
    public static function susun(?array $ubahan): array
    {
        $hasil = [];

        foreach (self::bawaan() as $ikon => $akun) {
            // Kolom yang dikosongkan pemakai berarti akun itu disembunyikan,
            // bukan kembali ke bawaan — kalau tidak, tidak ada cara membuang
            // satu akun dari satu thumbnail saja.
            $teks = trim((string) (($ubahan !== null && array_key_exists($ikon, $ubahan)) ? $ubahan[$ikon] : $akun));

            if ($teks !== '') {
                $hasil[] = ['ikon' => $ikon, 'akun' => $teks];
            }
        }

        return $hasil;
    }

    /**
     * Ubahan yang layak disimpan: null kalau sama persis dengan bawaan.
     *
     * Thumbnail yang tidak diubah akunnya tetap mengikuti konfigurasi, jadi
     * kalau suatu hari akun desa berganti nama, thumbnail lama di riwayat ikut
     * benar tanpa disunting satu per satu.
     *
     * @param  array<string, string|null>  $masukan
     * @return array<string, string>|null
     */
    public static function ubahan(array $masukan): ?array
    {
        $bawaan = self::bawaan();

        $bersih = collect($bawaan)
            ->map(fn (string $akun, string $ikon) => trim((string) ($masukan[$ikon] ?? '')))
            ->all();

        return $bersih === array_map('trim', $bawaan) ? null : $bersih;
    }
}
