<?php

namespace App\Providers;

use App\Contracts\PenulisBerita;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Diikat lewat kontrak supaya pengujian bisa menukarnya dengan penulis
        // tiruan — tanpa itu setiap kali rangkaian tes berjalan akan ada
        // panggilan ke layanan model, sebagian berbayar.
        $this->app->bind(PenulisBerita::class, function () {
            $driver = (string) config('penulis.driver');
            $kelas = config('penulis.penyedia.'.$driver);

            if (! $kelas) {
                throw new InvalidArgumentException(
                    "Penulis berita '{$driver}' tidak dikenal. Pilihannya: "
                    .implode(', ', array_keys(config('penulis.penyedia', [])))
                );
            }

            return $this->app->make($kelas);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
