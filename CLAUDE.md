# Warta

Alat internal untuk menyusun berita dan thumbnail-nya. Dua fitur:

1. **Thumbnail kolase** — beberapa foto disusun jadi satu gambar, dirender ke
   tiga ukuran siap bagikan. **Sudah jalan.**
2. **Generate berita dari 5W+1H** — belum dibangun.

## Stack

Laravel 13 · Livewire 4 · Tailwind 4 (Vite 8) · Intervention Image 4 · MySQL

Penamaan kelas, berkas, tabel, dan komentar memakai bahasa Indonesia, mengikuti
pola bimbel-ops (`PembuatThumbnail`, `TataLetakKolase`, tabel `thumbnail_foto`).

## Menjalankan

```sh
composer install && npm install
php artisan migrate
npm run dev          # atau: npm run build
php artisan serve
```

Basis data: `warta` (aplikasi) dan `warta_test` (pengujian), MySQL.

## Hal yang mudah salah

Empat hal berikut sudah pernah menggigit saat pembangunan. Semuanya gagal
diam-diam — tanpa galat, tanpa layar merah.

### 1. Nama kelas Tailwind tidak boleh dirangkai dari potongan

Pemindai Tailwind membaca berkas sebagai teks biasa. `'line-clamp-'.$n` tidak
pernah terlihat olehnya, jadi kelasnya tidak ikut terbangun dan pemangkas
barisnya lenyap begitu saja. Kembalikan nama kelas **utuh** dari PHP:

```php
// benar
return match ($this) { self::Story => 'line-clamp-3', ... };

// salah — kelasnya tidak akan pernah ada di CSS
return 'line-clamp-'.$this->barisJudul();
```

Berlaku juga untuk kelas grid di `TataLetakKolase`. Karena kelas-kelas itu
tinggal di `app/`, `resources/css/app.css` punya `@source '../../app'` — jangan
dihapus.

### 2. URL disk `public` sengaja relatif

`config/filesystems.php` memakai `'url' => '/storage'`, bukan bawaan Laravel
yang diawali `APP_URL`. Foto-foto itu dipasang di kanvas yang ditangkap menjadi
gambar di peramban; satu saja gambar dari asal berbeda membuat kanvasnya
ternajis dan **seluruh** penangkapan gagal. Jangan dikembalikan ke absolut.

### 3. Kanvas dikecilkan dengan transform, dan transform itu ikut tersalin

Pratinjau memakai `transform: scale()`. `modern-screenshot` menyalin gaya
terhitung ke klonanya, jadi tanpa `style: { transform: 'none' }` pada opsi
`domToBlob`, berkas 1200x630 hanya berisi gambar kecil di pojok kiri atas.
Lihat `resources/js/app.js`.

### 4. Pengujian wajib MySQL

`phpunit.xml` diarahkan ke `warta_test`, bukan sqlite — PHP di mesin
pengembangan ini tidak punya driver sqlite.

## Tata letak kolase

`app/Support/TataLetakKolase.php` menentukan susunan foto. Aturannya satu:
**susunan tidak diperkecil dari satu ukuran ke ukuran lain, arahnya membalik.**
Di Facebook yang melebar tiga foto berdampingan; di Story yang menjulang tiga
foto yang sama bertumpuk. Kalau disamakan, tiap sel Story menjadi pita sempit
yang memotong semua wajah.

Angka rasio pada komentar tiap cabang adalah rasio sel yang dihasilkan. Foto
berita umumnya 1.33–1.78; susunan dipilih yang selnya paling dekat ke rentang
itu. Kalau menambah ukuran baru, ia cukup jatuh ke salah satu `BentukKolase`
yang sudah ada.

Batas 4 foto (`TataLetakKolase::MAKS_FOTO`) dijaga di tiga tempat: pengunggah,
komponen kanvas, dan perata susunan.

## Satuan di dalam kanvas

Kanvas memasang `font-size` dari `UkuranThumbnail::basis()`, dan seluruh ukuran
teks serta jarak di dalamnya ditulis dalam `em`. Satu templat Blade melayani
ketiga ukuran tanpa percabangan — ganti basisnya, semuanya menyesuaikan.

Awas penggandaan: `em` pada elemen yang juga menyetel `text-[Xem]` dihitung
terhadap font-size elemen itu sendiri, bukan terhadap kanvas. Jangan
menyarangkan `text-[...]` di dalam `text-[...]`.

`tinggiPita()` bukan selera: angkanya hasil menjumlahkan isi terburuk yang masih
boleh muat. Kalau judul atau subjudul diperbesar, hitung ulang — kekurangan
belasan piksel tidak terlihat di pratinjau kecil, yang tampak hanya subjudul
yang hilang separuh di berkas jadinya.

## Perawatan

Foto ditulis ke disk saat diunggah, jauh sebelum tombol Simpan ditekan, supaya
kanvas punya URL sesama asal. Draf yang ditinggalkan meninggalkan berkas tanpa
pemilik:

```sh
php artisan warta:bersihkan-foto          # bertanya dulu
php artisan warta:bersihkan-foto --paksa  # untuk cron
```

## Yang belum dikerjakan

- Fitur generate berita 5W+1H (`ANTHROPIC_API_KEY` sudah disiapkan di `.env`).
- Autentikasi — saat ini semua rute terbuka.
- Penyusunan ulang foto memakai tombol naik/turun, belum seret-lepas.
