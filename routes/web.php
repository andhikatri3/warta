<?php

use App\Livewire\Thumbnail\DaftarThumbnail;
use App\Livewire\Thumbnail\PembuatThumbnail;
use Illuminate\Support\Facades\Route;

Route::get('/', PembuatThumbnail::class)->name('thumbnail.baru');
Route::get('/thumbnail/{thumbnail}', PembuatThumbnail::class)->name('thumbnail.sunting');
Route::get('/riwayat', DaftarThumbnail::class)->name('thumbnail.riwayat');
