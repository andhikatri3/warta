<?php

namespace App\Services\Thumbnail;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Menormalkan foto unggahan sebelum masuk ke kanvas.
 *
 * Foto tidak disimpan apa adanya karena tiga alasan yang semuanya baru
 * terlihat setelah gambar terlanjur salah:
 *
 * 1. Foto dari ponsel menyimpan arah putarnya di EXIF, bukan di pikselnya.
 *    Peramban menghormati EXIF saat menampilkan <img>, tetapi kanvas hasil
 *    tangkapan belum tentu — foto yang tegak di pratinjau bisa mendarat
 *    miring di berkas akhir. Memutar pikselnya sekarang menutup celah itu.
 * 2. Foto 12 MP yang dipakai sebagai satu sel kolase hanya membebani
 *    penangkapan di peramban; 2000 piksel sudah di atas sisi terpanjang
 *    kanvas mana pun.
 * 3. Menyeragamkan ke JPEG membuang metadata lokasi yang ikut menempel pada
 *    foto ponsel dan tidak ada urusannya dengan thumbnail berita.
 *
 * Logo diperlakukan lain: tetap PNG supaya latar transparannya utuh.
 */
class PenyimpanFoto
{
    public const SISI_MAKS = 2000;

    public const SISI_LOGO_MAKS = 480;

    public const MUTU = 85;

    public function simpanFoto(UploadedFile $berkas): string
    {
        $gambar = Image::decodePath($berkas->getRealPath())
            ->orient()
            ->scaleDown(width: self::SISI_MAKS, height: self::SISI_MAKS);

        return $this->tulis(
            (string) $gambar->encode(new JpegEncoder(quality: self::MUTU, progressive: true, strip: true)),
            'jpg',
        );
    }

    public function simpanLogo(UploadedFile $berkas): string
    {
        $gambar = Image::decodePath($berkas->getRealPath())
            ->orient()
            ->scaleDown(width: self::SISI_LOGO_MAKS, height: self::SISI_LOGO_MAKS);

        return $this->tulis((string) $gambar->encode(new PngEncoder), 'png');
    }

    public function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function tulis(string $isi, string $ekstensi): string
    {
        // Dikelompokkan per bulan supaya satu folder tidak menampung puluhan
        // ribu berkas, yang membuat penjelajah berkas di server merangkak.
        $path = 'thumbnail/'.now()->format('Y-m').'/'.Str::ulid().'.'.$ekstensi;

        Storage::disk('public')->put($path, $isi);

        return $path;
    }
}
