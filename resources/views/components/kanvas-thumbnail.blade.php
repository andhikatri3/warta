@props([
    'ukuran',
    'judul' => '',
    'subjudul' => null,
    'model' => null,
    'aksen' => null,
    'logo' => null,
    'foto' => [],
])

@php
    use App\Enums\Aksen;
    use App\Enums\ModelTeks;
    use App\Support\TataLetakKolase;

    $model ??= ModelTeks::Ceria;
    $aksen ??= Aksen::Biru;

    $foto = array_slice(array_values($foto), 0, TataLetakKolase::MAKS_FOTO);

    $letak = TataLetakKolase::untuk($ukuran, count($foto));
    $sosmed = config('warta.sosmed', []);

    // Bingkai kertas + bayangan tipis, hanya saat lebih dari satu foto. Foto
    // tunggal sudah memenuhi seluruh kanvas — menambah bingkai di situ hanya
    // menambah batas yang tidak perlu di sekeliling gambar yang memang harus
    // penuh. Ini keputusan otomatis dari jumlah foto, bukan pilihan yang perlu
    // diatur pemakai: tidak ada kolom baru, tidak ada kontrol baru di formulir.
    $berbingkai = count($foto) > 1;
@endphp

<div
    data-kanvas="{{ $ukuran->value }}"
    data-lebar="{{ $ukuran->lebar() }}"
    data-tinggi="{{ $ukuran->tinggi() }}"
    data-model="{{ $model->value }}"
    style="width: {{ $ukuran->lebar() }}px; height: {{ $ukuran->tinggi() }}px; font-size: {{ $ukuran->basis() }}px; --aksen: {{ $aksen->heks() }};"
    class="relative flex shrink-0 flex-col overflow-hidden bg-white font-sans antialiased"
>
    {{-- Kolase mengisi seluruh kanvas. Jarak antarsel dibuat lewat gap di atas
         latar putih, jadi garis pemisahnya adalah latar yang menembus, bukan
         border yang harus dihitung ulang tiap kali susunannya berubah. --}}
    <div class="absolute inset-0 grid gap-[0.045em] bg-white {{ $letak['wadah'] }}">
        @forelse ($foto as $i => $f)
            {{-- Bingkai dibuat lewat padding pada sel luar (kertas putih di
                 sekeliling) plus sudut membulat + bayangan pada pembungkus
                 dalam (foto tampak tertempel, bukan menyatu rata dengan sel
                 di sebelahnya). Satu markup untuk kedua kondisi, tinggal kelas
                 mana yang aktif — supaya tidak ada gambar yang dirender dua
                 kali dan perbedaan foto tunggal vs kolase hanya soal kelas. --}}
            <div class="relative overflow-hidden {{ $letak['sel'][$i] ?? '' }} {{ $berbingkai ? 'bg-white p-[0.07em]' : '' }}">
                <div class="h-full w-full overflow-hidden bg-slate-200 {{ $berbingkai ? 'rounded-[0.045em] shadow-[0_0.055em_0.13em_rgb(0_0_0/0.4)]' : '' }}">
                    <img
                        src="{{ $f['url'] }}"
                        alt=""
                        class="h-full w-full object-cover"
                        style="object-position: {{ $f['fokus_x'] ?? 50 }}% {{ $f['fokus_y'] ?? 50 }}%;"
                    >
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center bg-slate-100">
                <span class="text-[0.28em] font-semibold tracking-wide text-slate-400">Belum ada foto</span>
            </div>
        @endforelse
    </div>

    {{-- Judul ditumpuk di atas foto. Yang membuatnya terbaca bukan gradien ini
         saja, melainkan garis tepi tebal pada hurufnya — lihat .judul-hias di
         app.css. Gradiennya lapis kedua, untuk foto yang bagian bawahnya sangat
         terang. --}}
    <div class="absolute inset-x-0 bottom-0 flex items-end gap-[0.45em] px-[0.6em] pb-[0.6em] pt-[1.7em]"
         style="background: linear-gradient(to top, rgba(2,6,23,0.88) 0%, rgba(2,6,23,0.62) 42%, rgba(2,6,23,0) 100%);">
        <div class="min-w-0 flex-1">
            {{-- Judul kosong tidak menyisakan apa pun: tidak ada teks contoh,
                 tidak ada elemen kosong yang tetap memakan ruang, dan palang
                 aksennya ikut hilang. Thumbnail tanpa judul memang dipakai
                 untuk foto yang bicara sendiri — palang yang menggantung
                 sendirian di situ akan tampak seperti sisa yang lupa dibuang. --}}
            @if (trim($judul) !== '')
                <span
                    class="mb-[0.28em] block h-[0.085em] w-[1.5em] rounded-full [box-shadow:0_0.02em_0.04em_rgb(0_0_0/0.6)]"
                    style="background: {{ $aksen->heks() }};"
                ></span>

                <h2 class="judul-kanvas judul-hias {{ $ukuran->klemJudul() }} text-[1.15em] leading-[1.06] tracking-[-0.015em]">
                    <span class="judul-isi">{{ $judul }}</span>
                </h2>
            @endif

            @if ($subjudul)
                <p class="{{ trim($judul) !== '' ? 'mt-[0.45em]' : '' }} {{ $ukuran->klemSubjudul() }} text-[0.3em] font-semibold leading-[1.4] text-white [text-shadow:0_0.06em_0.08em_rgb(0_0_0/0.8)]">
                    {{ $subjudul }}
                </p>
            @endif

            {{-- Akun resmi desa. Ikonnya ikut warna aksen, teksnya tetap putih
                 supaya terbaca di atas foto apa pun. --}}
            @if ($sosmed !== [])
                <div class="mt-[0.32em] flex flex-wrap items-center gap-x-[0.45em] gap-y-[0.12em]">
                    @foreach ($sosmed as $s)
                        <span class="flex items-center gap-[0.34em] text-[0.23em] font-bold leading-none text-white [text-shadow:0_0.07em_0.09em_rgb(0_0_0/0.85)]">
                            <x-dynamic-component
                                :component="'ikon.'.$s['ikon']"
                                class="size-[1.3em] shrink-0 [filter:drop-shadow(0_0.06em_0.08em_rgb(0_0_0/0.9))]"
                                style="color: {{ $aksen->heks() }};"
                            />
                            {{ $s['akun'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($logo)
            <img src="{{ $logo }}" alt="" class="h-[0.95em] w-auto shrink-0 object-contain [filter:drop-shadow(0_0.04em_0.06em_rgb(0_0_0/0.5))]">
        @endif
    </div>
</div>
