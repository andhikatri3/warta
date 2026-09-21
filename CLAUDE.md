# Warta

Alat internal untuk menyusun berita dan thumbnail-nya. Dua fitur:

1. **Thumbnail kolase** — beberapa foto disusun jadi satu gambar, dirender ke
   tiga ukuran siap bagikan.
2. **Menulis berita dari 5W+1H** — bahan disusun model Claude menjadi naskah
   berikut tiga alternatif judul.

Keduanya sudah jalan dan saling tersambung: tombol di halaman berita membuka
pembuat thumbnail dengan judulnya sudah terisi.

## Stack

Laravel 13 · Livewire 4 · Tailwind 4 (Vite 8) · Intervention Image 4 ·
Anthropic PHP SDK · MySQL

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

Menulis berita butuh `ANTHROPIC_API_KEY` di `.env`. Tanpa kunci, halamannya
tetap terbuka dan formulirnya tetap bisa diisi — yang muncul pesan yang
menjelaskan kunci belum diisi, bukan layar galat.

## Menulis berita

Pembagian tugasnya disengaja: **penyusun bertanggung jawab atas kebenaran
fakta, model hanya atas bentuk kalimat.** Model tidak meliput, tidak mencari
data, dan tidak tahu apa pun di luar `BahanBerita`. Prompt sistem di
`PenulisClaude` melarangnya menambah nama, angka, tanggal, tempat, atau
lembaga yang tidak ada di bahan, dan melarangnya mengarang kutipan.

Dua rinci yang mudah dianggap remeh:

- **Ruas kosong tidak dikirim.** `BahanBerita::sebagaiDaftar()` membuang ruas
  yang tidak diisi alih-alih mengirim labelnya dengan isi kosong. Label kosong
  mengundang model mengisinya sendiri.
- **Kutipan disalin persis.** Kutipan dikirim terpisah dari bahan, dengan
  perintah menyalin huruf demi huruf; model hanya boleh menambahkan kalimat
  pengantar.

Naskahnya diminta lewat structured output (`outputConfig.format` dengan skema
JSON), bukan diurai dari teks bebas. Catatan SDK: `parsedOutput()` hanya
bekerja untuk kelas `StructuredOutputModel`; dengan skema mentah seperti di
sini, isinya dibaca dari blok teks pertama lalu `json_decode` sendiri.

Bahan 5W+1H ikut tersimpan di kolom `bahan` bersama naskahnya, jadi asal tiap
kalimat bisa ditelusuri dan berita bisa ditulis ulang dengan gaya lain tanpa
mengetik faktanya dari awal.

Penulisnya di balik kontrak `App\Contracts\PenulisBerita`. Pengujian menukarnya
dengan `Tests\Dukungan\PenulisTiruan` — **rangkaian tes tidak boleh pernah
memanggil layanan berbayar.** Kalau menambah tes yang menyentuh jalur ini,
pasang tiruannya lewat `$this->app->instance(...)`.

Model dan kedalaman berpikirnya diatur di `config/anthropic.php`. `effort`
ditahan di `medium` karena tugasnya mengarang dari fakta yang sudah ada, bukan
menalar; naikkan kalau hasilnya dangkal.

## Hal yang mudah salah

Tujuh hal berikut sudah pernah menggigit saat pembangunan. Semuanya gagal
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

### 4. Intervention Image 4.3 memakai `decodePath()`, bukan `read()`

`ImageManager::read()` yang ada di Intervention Image 4.0–4.2 **dihapus** di
4.3; penggantinya `decode()` dengan varian `decodePath()`, `decodeBinary()`,
dan seterusnya. Banyak contoh di luar sana masih memakai `read()`, dan
kesalahannya baru muncul saat ada yang benar-benar mengunggah foto.

Karena itu `PenyimpanFotoTest` mengunggah berkas gambar sungguhan, bukan tiruan.
Apa pun yang menyentuh pustaka gambar harus dilalui tes yang benar-benar
menghasilkan berkas — membaca tanda tangan metodenya saja tidak cukup.

### 5. Webfont butuh `Vite::fonts()` di layout

`laravel-vite-plugin` membangun huruf menjadi berkas CSS tersendiri yang tidak
dirujuk `app.css`. Tanpa `{{ Vite::fonts() }}` di `<head>`, tidak ada satu pun
webfont yang termuat — halaman diam-diam jatuh ke huruf sistem dan semua model
teks tampak seragam. Gejalanya halus: halamannya tetap tampak wajar, hanya
hurufnya bukan yang dimaksud. Periksa dengan melihat `document.fonts`, bukan
dengan mata.

### 6. Pembaruan Livewire menghapus atribut style yang ditulis JavaScript

Livewire mencocokkan DOM ke HTML kiriman server, termasuk atribut `style`. Nilai
`--skala` yang dipasang `app.js` pada wadah pratinjau ikut terhapus setiap kali
pemakai mengetik judul, dan kanvas kembali ke ukuran asli lalu tampak terpotong.
`ResizeObserver` tidak menyelamatkannya karena lebar wadah tidak berubah.

Karena itu `pasangPratinjau()` menghitung skala **langsung**, dan pengamat DOM
di bawahnya mengawasi `attributes: ['style']`, bukan hanya `childList`.
Penjaganya ada di `hitungSkala()`: nilai yang sudah sama tidak ditulis ulang —
tanpa itu pengamat dan penulis saling memanggil tanpa henti.

### 7. Pengujian wajib MySQL

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

## Model teks

`ModelTeks` memilih huruf judul dan perlakuannya lewat atribut `data-model` di
kanvas; aturannya sendiri ada di `resources/css/app.css`. Ditulis sebagai CSS
biasa, bukan utility, karena `-webkit-text-stroke`, `paint-order`, dan gradien
yang dipotong bentuk huruf memang tidak punya utility-nya.

Dua kelas dipisah dengan sengaja: `.judul-kanvas` hanya membawa hurufnya,
`.judul-hias` membawa garis tepi dan gradien. Pemisahan itu sisa dari masa
ketika ada varian pita berlatar putih, dan tetap dipertahankan karena berguna:
huruf dan hiasan memang dua hal berbeda.

Garis tepi tebal itu yang membuat judul boleh ditumpuk di atas kolase sebagai
satu-satunya tata letak. Tanpa hiasan ini, judul polos bisa mendarat tepat di
atas wajah dan varian pita terpisah jadi perlu.

**Aksen harus punya pekerjaan yang terlihat.** Ia pernah diam-diam jadi hiasan
tanpa fungsi: waktu lencana label dibuang, satu-satunya sisa pemakainya adalah
garis tepi huruf model Ceria, jadi pada tiga model lain memilih warna tidak
mengubah apa pun. Sekarang aksen mewarnai palang di atas judul dan ikon akun —
keduanya ada di semua model — dan ada tes regresi yang membandingkan hasil
render dua aksen berbeda untuk tiap model.

## Satuan di dalam kanvas

Kanvas memasang `font-size` dari `UkuranThumbnail::basis()`, dan seluruh ukuran
teks serta jarak di dalamnya ditulis dalam `em`. Satu templat Blade melayani
ketiga ukuran tanpa percabangan — ganti basisnya, semuanya menyesuaikan.

Awas penggandaan: `em` pada elemen yang juga menyetel `text-[Xem]` dihitung
terhadap font-size elemen itu sendiri, bukan terhadap kanvas. Jangan
menyarangkan `text-[...]` di dalam `text-[...]`.

Blok teks duduk di dasar kanvas dan tumbuh ke atas, jadi tidak ada tinggi tetap
yang harus dihitung ulang. Yang menjaganya tetap terkendali adalah `klemJudul()`
dan `klemSubjudul()`: luapan di kanvas berukuran tetap tidak memunculkan bilah
gulir, ia hanya terpotong diam-diam di tempat yang tidak terduga.

## Akun resmi di kanvas

Baris akun di bawah subjudul datang dari `config/warta.php`, bukan dari
formulir maupun basis data: nilainya sama untuk semua thumbnail dan hampir tak
pernah berubah, jadi mengetiknya ulang tiap kali hanya membuka peluang salah
ketik yang baru ketahuan setelah gambarnya tersebar. Ganti lewat `.env`
(`WARTA_FACEBOOK`, `WARTA_INSTAGRAM`, `WARTA_WEB`); akun yang dikosongkan
hilang sendiri dari barisnya, tanpa menyisakan ikon yatim.

Ikonnya komponen Blade di `resources/views/components/ikon/`, dipanggil lewat
`<x-dynamic-component>` dari nilai `ikon` pada konfigurasi. SVG sebaris, bukan
berkas terpisah — berkas gambar lintas asal akan menajiskan kanvas saat
ditangkap, sama seperti foto.

Label dan tanggal **sudah dicabut** dari kanvas beserta kolomnya. Kalau nanti
diminta kembali, migrasi `2026_09_21_000005` punya `down()` yang memulihkan
kolomnya.

## Perawatan

Foto ditulis ke disk saat diunggah, jauh sebelum tombol Simpan ditekan, supaya
kanvas punya URL sesama asal. Draf yang ditinggalkan meninggalkan berkas tanpa
pemilik:

```sh
php artisan warta:bersihkan-foto          # bertanya dulu
php artisan warta:bersihkan-foto --paksa  # untuk cron
```

## Yang belum dikerjakan

- Autentikasi — saat ini semua rute terbuka.
- Jalur penulisan berita belum pernah dijalankan dengan kunci API sungguhan;
  yang teruji baru jalur di sekitarnya lewat penulis tiruan.
- Penyusunan ulang foto memakai tombol naik/turun, belum seret-lepas.
- Judul selalu rata kiri bawah; belum ada pilihan perataan atau pita miring
  seperti poster desa pada umumnya.
