@props([
    'ukuran',
    'judul' => '',
    'subjudul' => null,
    'label' => null,
    'tanggal' => null,
    'gaya' => null,
    'aksen' => null,
    'logo' => null,
    'foto' => [],
])

@php
    use App\Enums\Aksen;
    use App\Enums\GayaThumbnail;
    use App\Support\TataLetakKolase;

    $gaya ??= GayaThumbnail::PitaTerang;
    $aksen ??= Aksen::Biru;

    // Overlay tidak pernah rapi di atas kolase, jadi begitu fotonya lebih dari
    // satu ia diturunkan ke pita. Penjagaan ada di sini, bukan hanya di
    // pemilihnya, supaya thumbnail lama yang tersimpan dengan gaya overlay lalu
    // ditambahi foto tetap terender benar.
    $foto = array_slice(array_values($foto), 0, TataLetakKolase::MAKS_FOTO);
    if (count($foto) > 1 && ! $gaya->cocokUntukKolase()) {
        $gaya = GayaThumbnail::PitaTerang;
    }

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
    style="width: {{ $ukuran->lebar() }}px; height: {{ $ukuran->tinggi() }}px; font-size: {{ $ukuran->basis() }}px;"
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

                <h2 class="mt-[0.17em] {{ $klemJudul }} text-[0.76em] font-extrabold leading-[1.13] tracking-[-0.025em] {{ $terang ? 'text-slate-900' : 'text-white' }}">
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
        {{-- Gaya overlay: judul ditumpuk di atas foto. Gradiennya dibuat tinggi
             dan pekat di dasar supaya teks tetap terbaca di atas foto terang. --}}
        <div class="absolute inset-x-0 bottom-0 flex items-end gap-[0.45em] px-[0.6em] pb-[0.55em] pt-[1.6em]"
             style="background: linear-gradient(to top, rgba(2,6,23,0.95) 0%, rgba(2,6,23,0.75) 45%, rgba(2,6,23,0) 100%);">
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
                            <span class="text-[0.2em] font-semibold text-slate-300">{{ $tanggal }}</span>
                        @endif
                    </div>
                @endif

                <h2 class="mt-[0.17em] {{ $klemJudul }} text-[0.76em] font-extrabold leading-[1.13] tracking-[-0.025em] text-white">
                    {{ $judul ?: 'Judul berita' }}
                </h2>

                @if ($subjudul)
                    <p class="mt-[0.5em] {{ $klemSubjudul }} text-[0.26em] font-medium leading-[1.4] text-slate-300">
                        {{ $subjudul }}
                    </p>
                @endif
            </div>

            @if ($logo)
                <img src="{{ $logo }}" alt="" class="h-[0.95em] w-auto shrink-0 object-contain">
            @endif
        </div>
    @endif
</div>
