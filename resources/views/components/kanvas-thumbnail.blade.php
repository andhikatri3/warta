@props([
    'ukuran',
    'judul' => '',
    'subjudul' => null,
    'label' => null,
    'tanggal' => null,
    'gaya' => null,
    'model' => null,
    'aksen' => null,
    'logo' => null,
    'foto' => [],
])

@php
    use App\Enums\Aksen;
    use App\Enums\GayaThumbnail;
    use App\Enums\ModelTeks;
    use App\Support\TataLetakKolase;

    $gaya ??= GayaThumbnail::Overlay;
    $model ??= ModelTeks::Ceria;
    $aksen ??= Aksen::Biru;

    $foto = array_slice(array_values($foto), 0, TataLetakKolase::MAKS_FOTO);

    $letak = TataLetakKolase::untuk($ukuran, count($foto));
    $berpita = $gaya->berpita();
    $terang = $gaya === GayaThumbnail::PitaTerang;
    $tinggiKolase = $berpita ? $ukuran->tinggi() - $ukuran->tinggiPita() : $ukuran->tinggi();
    $klemJudul = $ukuran->klemJudul();
    $klemSubjudul = $ukuran->klemSubjudul();
@endphp

<div
    data-kanvas="{{ $ukuran->value }}"
    data-lebar="{{ $ukuran->lebar() }}"
    data-tinggi="{{ $ukuran->tinggi() }}"
    data-model="{{ $model->value }}"
    style="width: {{ $ukuran->lebar() }}px; height: {{ $ukuran->tinggi() }}px; font-size: {{ $ukuran->basis() }}px; --aksen: {{ $aksen->heks() }};"
    class="relative flex shrink-0 flex-col overflow-hidden bg-white font-sans antialiased"
>
    {{-- Kolase. Jarak antarsel dibuat lewat gap di atas latar putih, jadi
         garis pemisahnya adalah latar yang menembus, bukan border yang harus
         dihitung ulang tiap kali susunannya berubah. --}}
    <div
        class="grid gap-[0.045em] bg-white {{ $letak['wadah'] }} {{ $berpita ? 'shrink-0' : 'absolute inset-0' }}"
        style="height: {{ $tinggiKolase }}px;"
    >
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

    @if ($berpita)
        <div
            class="relative flex shrink-0 items-center gap-[0.45em] overflow-hidden px-[0.6em] py-[0.28em] {{ $terang ? 'bg-white' : 'bg-slate-900' }}"
            style="height: {{ $ukuran->tinggiPita() }}px;"
        >
            <span class="absolute inset-x-0 top-0" style="height: 0.09em; background: {{ $aksen->heks() }};"></span>

            <div class="min-w-0 flex-1">
                @if ($label || $tanggal)
                    <div class="flex items-center gap-[0.25em]">
                        @if ($label)
                            <span
                                class="rounded-[0.15em] px-[0.7em] py-[0.32em] text-[0.19em] font-bold uppercase leading-none tracking-[0.13em] text-white"
                                style="background: {{ $aksen->heks() }};"
                            >{{ $label }}</span>
                        @endif
                        @if ($tanggal)
                            <span class="text-[0.2em] font-semibold {{ $terang ? 'text-slate-400' : 'text-slate-500' }}">{{ $tanggal }}</span>
                        @endif
                    </div>
                @endif

                {{-- Di dalam pita judul hanya memakai hurufnya, tanpa garis tepi:
                     latarnya sudah polos, jadi tidak ada yang perlu dilawan. --}}
                <h2 class="judul-kanvas mt-[0.17em] {{ $klemJudul }} text-[0.76em] leading-[1.13] tracking-[-0.025em] {{ $terang ? 'text-slate-900' : 'text-white' }}">
                    {{ $judul ?: 'Judul berita' }}
                </h2>

                @if ($subjudul)
                    <p class="mt-[0.5em] {{ $klemSubjudul }} text-[0.26em] font-medium leading-[1.4] {{ $terang ? 'text-slate-500' : 'text-slate-400' }}">
                        {{ $subjudul }}
                    </p>
                @endif
            </div>

            @if ($logo)
                <img src="{{ $logo }}" alt="" class="h-[0.95em] w-auto shrink-0 object-contain">
            @endif
        </div>
    @else
        {{-- Judul ditumpuk di atas foto. Yang membuatnya terbaca bukan gradien
             ini saja, melainkan garis tepi tebal pada hurufnya — lihat
             .judul-hias di app.css. Gradiennya tetap dipasang sebagai lapis
             kedua untuk foto yang bagian bawahnya sangat terang. --}}
        <div class="absolute inset-x-0 bottom-0 flex items-end gap-[0.45em] px-[0.6em] pb-[0.6em] pt-[1.7em]"
             style="background: linear-gradient(to top, rgba(2,6,23,0.88) 0%, rgba(2,6,23,0.62) 42%, rgba(2,6,23,0) 100%);">
            <div class="min-w-0 flex-1">
                @if ($label || $tanggal)
                    <div class="flex items-center gap-[0.25em]">
                        @if ($label)
                            <span
                                class="rounded-[0.15em] px-[0.7em] py-[0.32em] text-[0.19em] font-bold uppercase leading-none tracking-[0.13em] text-white"
                                style="background: {{ $aksen->heks() }};"
                            >{{ $label }}</span>
                        @endif
                        @if ($tanggal)
                            <span class="text-[0.2em] font-semibold text-slate-200 [text-shadow:0_0.05em_0.06em_rgb(0_0_0/0.7)]">{{ $tanggal }}</span>
                        @endif
                    </div>
                @endif

                <h2 class="judul-kanvas judul-hias mt-[0.22em] {{ $klemJudul }} text-[1.15em] leading-[1.06] tracking-[-0.015em]">
                    {{ $judul ?: 'Judul berita' }}
                </h2>

                @if ($subjudul)
                    <p class="mt-[0.45em] {{ $klemSubjudul }} text-[0.3em] font-semibold leading-[1.4] text-white [text-shadow:0_0.06em_0.08em_rgb(0_0_0/0.8)]">
                        {{ $subjudul }}
                    </p>
                @endif
            </div>

            @if ($logo)
                <img src="{{ $logo }}" alt="" class="h-[0.95em] w-auto shrink-0 object-contain [filter:drop-shadow(0_0.04em_0.06em_rgb(0_0_0/0.5))]">
            @endif
        </div>
    @endif
</div>
