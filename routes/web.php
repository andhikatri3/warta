<?php

use App\Livewire\Berita\DaftarBerita;
use App\Livewire\Berita\PembuatBerita;
use App\Livewire\Thumbnail\DaftarThumbnail;
use App\Livewire\Thumbnail\PembuatThumbnail;
use Illuminate\Support\Facades\Route;

Route::get('/', PembuatBerita::class)->name('berita.baru');
Route::get('/berita/{berita}', PembuatBerita::class)->name('berita.sunting');
Route::get('/berita', DaftarBerita::class)->name('berita.riwayat');

Route::get('/thumbnail', PembuatThumbnail::class)->name('thumbnail.baru');
Route::get('/thumbnail/{thumbnail}', PembuatThumbnail::class)->name('thumbnail.sunting');
Route::get('/riwayat', DaftarThumbnail::class)->name('thumbnail.riwayat');
