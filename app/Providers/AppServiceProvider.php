<?php

namespace App\Providers;

use App\Contracts\PenulisBerita;
use App\Services\Berita\PenulisClaude;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Diikat lewat kontrak supaya pengujian bisa menukarnya dengan penulis
        // tiruan — tanpa itu setiap kali rangkaian tes berjalan akan ada
        // panggilan berbayar ke layanan model.
        $this->app->bind(PenulisBerita::class, PenulisClaude::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
