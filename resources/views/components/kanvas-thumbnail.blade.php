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
            <div class="relative overflow-hidden bg-slate-200 {{ $letak['sel'][$i] ?? '' }}">
                <img
                    src="{{ $f['url'] }}"
                    alt=""
                    class="h-full w-full object-cover"
                    style="object-position: {{ $f['fokus_x'] ?? 50 }}% {{ $f['fokus_y'] ?? 50 }}%;"
                >
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
            {{-- Palang aksen. Ini yang membuat pilihan warna terlihat pada
                 semua model teks, bukan hanya pada Ceria yang kebetulan
                 memakai aksen sebagai garis tepi hurufnya. --}}
            <span
                class="mb-[0.28em] block h-[0.085em] w-[1.5em] rounded-full [box-shadow:0_0.02em_0.04em_rgb(0_0_0/0.6)]"
                style="background: {{ $aksen->heks() }};"
            ></span>

            <h2 class="judul-kanvas judul-hias {{ $ukuran->klemJudul() }} text-[1.15em] leading-[1.06] tracking-[-0.015em]">
                {{ $judul ?: 'Judul berita' }}
            </h2>

            @if ($subjudul)
                <p class="mt-[0.45em] {{ $ukuran->klemSubjudul() }} text-[0.3em] font-semibold leading-[1.4] text-white [text-shadow:0_0.06em_0.08em_rgb(0_0_0/0.8)]">
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
