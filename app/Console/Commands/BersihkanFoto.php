<?php

namespace App\Console\Commands;

use App\Models\Thumbnail;
use App\Models\ThumbnailFoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Membuang foto yang sudah tidak ditunjuk baris mana pun.
 *
 * Foto diunggah dan langsung ditulis ke disk supaya kanvas punya URL sesama
 * asal untuk ditangkap — jauh sebelum penyusunnya menekan Simpan. Kalau ia
 * lalu menutup tab, berkasnya tetap tinggal tanpa ada baris yang memilikinya.
 *
 * Batas usia menjaga agar penyusunan yang sedang berjalan tidak ikut kena:
 * foto yang baru diunggah lima menit lalu memang belum punya baris, tapi
 * mungkin sedang tampil di layar seseorang.
 */
class BersihkanFoto extends Command
{
    protected $signature = 'warta:bersihkan-foto {--jam=24 : Usia minimum berkas yang boleh dibuang}
                                                 {--paksa : Hapus tanpa bertanya}';

    protected $description = 'Hapus foto telantar yang tidak dimiliki thumbnail mana pun';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $batas = now()->subHours((int) $this->option('jam'));

        $dipakai = ThumbnailFoto::pluck('path')
            ->merge(Thumbnail::whereNotNull('logo')->pluck('logo'))
            ->flip();

        $telantar = collect($disk->allFiles('thumbnail'))
            ->reject(fn (string $path) => $dipakai->has($path))
            ->filter(fn (string $path) => $disk->lastModified($path) < $batas->getTimestamp())
            ->values();

        if ($telantar->isEmpty()) {
            $this->info('Tidak ada berkas telantar.');

            return self::SUCCESS;
        }

        $ukuran = $telantar->sum(fn (string $path) => $disk->size($path));

        $this->line($telantar->count().' berkas telantar, total '.round($ukuran / 1048576, 1).' MB.');

        if (! $this->option('paksa') && ! $this->confirm('Hapus sekarang?', true)) {
            return self::SUCCESS;
        }

        $disk->delete($telantar->all());
        $this->info($telantar->count().' berkas dihapus.');

        return self::SUCCESS;
    }
}
