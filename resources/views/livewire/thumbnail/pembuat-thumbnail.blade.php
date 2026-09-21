@php
    use App\Enums\Aksen;
    use App\Enums\GayaThumbnail;
    use App\Enums\ModelTeks;
    use App\Support\TataLetakKolase;

    $kelasLabel = 'mb-1.5 block text-xs font-semibold text-slate-700';
    $kelasInput = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10';
    $kelasGalat = 'mt-1 text-xs font-medium text-red-600';

    $namaDasar = str()->slug($judul) ?: 'thumbnail';
    $jumlahFoto = count($foto);
    $penuh = $jumlahFoto >= TataLetakKolase::MAKS_FOTO;
@endphp

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,380px)_minmax(0,1fr)]">

    {{-- ================= Panel penyusun ================= --}}
    <div class="space-y-4 lg:sticky lg:top-20">

        @if (session('sukses'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                {{ session('sukses') }}
            </div>
        @endif

        {{-- ---------- Teks ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Teks</h2>

            <div class="mb-3">
                <label for="judul" class="{{ $kelasLabel }}">Judul berita</label>
                <textarea
                    id="judul"
                    rows="2"
                    wire:model.live.debounce.400ms="judul"
                    placeholder="RSUD Resmikan Gedung Layanan Jantung Terpadu"
                    class="{{ $kelasInput }} resize-none"
                ></textarea>
                @error('judul') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label for="subjudul" class="{{ $kelasLabel }}">Subjudul <span class="font-normal text-slate-400">— opsional</span></label>
                <input
                    id="subjudul"
                    type="text"
                    wire:model.live.debounce.400ms="subjudul"
                    placeholder="Melayani 200 pasien per hari"
                    class="{{ $kelasInput }}"
                >
                @error('subjudul') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="label" class="{{ $kelasLabel }}">Label</label>
                    <input
                        id="label"
                        type="text"
                        wire:model.live.debounce.400ms="label"
                        placeholder="BERITA"
                        class="{{ $kelasInput }}"
                    >
                    @error('label') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal" class="{{ $kelasLabel }}">Tanggal</label>
                    <input id="tanggal" type="date" wire:model.live="tanggal" class="{{ $kelasInput }}">
                    @error('tanggal') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ---------- Foto ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-baseline gap-2">
                <h2 class="text-sm font-bold text-slate-900">Foto</h2>
                <span class="text-xs font-medium text-slate-400">{{ $jumlahFoto }} dari {{ TataLetakKolase::MAKS_FOTO }}</span>
            </div>

            @if ($jumlahFoto > 0)
                <ul class="mb-3 space-y-2">
                    @foreach ($foto as $i => $f)
                        <li class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                            <div class="flex items-center gap-2.5">
                                <span class="grid size-5 shrink-0 place-items-center rounded bg-slate-900 text-[10px] font-bold text-white">
                                    {{ $i + 1 }}
                                </span>

                                <img
                                    src="{{ $f['url'] }}"
                                    alt=""
                                    class="size-11 shrink-0 rounded-md object-cover ring-1 ring-slate-200"
                                    style="object-position: {{ $f['fokus_x'] }}% {{ $f['fokus_y'] }}%;"
                                >

                                <div class="min-w-0 flex-1 text-xs text-slate-500">
                                    @if ($jumlahFoto === 1)
                                        <span class="font-semibold text-slate-700">Foto tunggal</span>
                                    @elseif ($i === 0)
                                        <span class="font-semibold text-slate-700">Foto utama</span>
                                    @else
                                        <span>Pendamping</span>
                                    @endif
                                    <span class="block text-[11px] text-slate-400">fokus {{ $f['fokus_x'] }}% / {{ $f['fokus_y'] }}%</span>
                                </div>

                                <div class="flex shrink-0 items-center gap-0.5">
                                    <button
                                        type="button"
                                        wire:click="geserFoto({{ $i }}, {{ $i - 1 }})"
                                        @disabled($i === 0)
                                        title="Naikkan urutan"
                                        class="grid size-7 place-items-center rounded text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-30"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="geserFoto({{ $i }}, {{ $i + 1 }})"
                                        @disabled($i === $jumlahFoto - 1)
                                        title="Turunkan urutan"
                                        class="grid size-7 place-items-center rounded text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-30"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="bukaFokus({{ $i }})"
                                        title="Atur titik fokus"
                                        @class([
                                            'grid size-7 place-items-center rounded transition',
                                            'bg-slate-900 text-white' => $fokusAktif === $i,
                                            'text-slate-500 hover:bg-slate-200 hover:text-slate-900' => $fokusAktif !== $i,
                                        ])
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="hapusFoto({{ $i }})"
                                        title="Hapus foto"
                                        class="grid size-7 place-items-center rounded text-slate-500 transition hover:bg-red-100 hover:text-red-600"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5h6v2m-7 0v12h8V7"/></svg>
                                    </button>
                                </div>
                            </div>

                            @if ($fokusAktif === $i)
                                <div class="mt-2 border-t border-slate-200 pt-2">
                                    <p class="mb-2 text-[11px] leading-snug text-slate-500">
                                        Klik bagian yang harus selalu terlihat. Tiap sel kolase memotong foto ini
                                        dengan bentuk berbeda, jadi titik inilah yang dipertahankan di semua ukuran.
                                    </p>
                                    <div data-fokus="{{ $i }}" class="relative cursor-crosshair overflow-hidden rounded-md ring-1 ring-slate-200">
                                        <img src="{{ $f['url'] }}" alt="" class="block w-full select-none">
                                        <span
                                            class="pointer-events-none absolute size-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-blue-600 shadow-md"
                                            style="left: {{ $f['fokus_x'] }}%; top: {{ $f['fokus_y'] }}%;"
                                        ></span>
                                    </div>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <label @class([
                'flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed px-3 py-3 text-xs font-semibold transition',
                'border-slate-300 text-slate-500 hover:border-slate-900 hover:text-slate-900' => ! $penuh,
                'pointer-events-none border-slate-200 text-slate-300' => $penuh,
            ])>
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                {{ $penuh ? 'Batas '.TataLetakKolase::MAKS_FOTO.' foto tercapai' : 'Tambah foto' }}
                <input type="file" wire:model="unggahan" multiple accept="image/jpeg,image/png,image/webp" class="hidden" @disabled($penuh)>
            </label>

            <div wire:loading wire:target="unggahan" class="mt-2 text-xs font-medium text-slate-500">Memproses foto…</div>
            @error('unggahan') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            @error('unggahan.*') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
        </section>

        {{-- ---------- Tampilan ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Tampilan</h2>

            <div class="mb-4">
                <span class="{{ $kelasLabel }}">Model teks</span>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach (ModelTeks::cases() as $m)
                        <button
                            type="button"
                            wire:click="$set('model', '{{ $m->value }}')"
                            title="{{ $m->keterangan() }} — cocok untuk {{ $m->cocokUntuk() }}"
                            data-model="{{ $m->value }}"
                            style="--aksen: {{ $this->aksenTerpilih->heks() }};"
                            @class([
                                'rounded-lg border bg-white p-1.5 transition',
                                'border-slate-900 ring-2 ring-slate-900/15' => $this->modelTerpilih === $m,
                                'border-slate-200 hover:border-slate-400' => $this->modelTerpilih !== $m,
                            ])
                        >
                            {{-- Contohnya diletakkan di atas bidang gelap, meniru
                                 keadaan sebenarnya: model teks ini memang dipakai
                                 menumpuk foto, bukan di atas kertas putih. --}}
                            <span class="grid h-9 place-items-center rounded-md bg-slate-700">
                                <span class="judul-kanvas judul-hias text-[20px] leading-none">{{ $m->contoh() }}</span>
                            </span>
                            <span class="mt-1.5 block text-[10px] font-bold leading-tight text-slate-600">{{ $m->label() }}</span>
                        </button>
                    @endforeach
                </div>
                <p class="mt-1.5 text-[11px] leading-snug text-slate-400">
                    {{ $this->modelTerpilih->keterangan() }} — cocok untuk {{ $this->modelTerpilih->cocokUntuk() }}.
                </p>
            </div>

            <div class="mb-4">
                <span class="{{ $kelasLabel }}">Letak judul</span>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach (GayaThumbnail::cases() as $g)
                        <button
                            type="button"
                            wire:click="$set('gaya', '{{ $g->value }}')"
                            title="{{ $g->keterangan() }}"
                            @class([
                                'rounded-lg border px-2 py-2 text-left transition',
                                'border-slate-900 bg-slate-900 text-white' => $this->gayaTerpilih === $g,
                                'border-slate-200 text-slate-700 hover:border-slate-400' => $this->gayaTerpilih !== $g,
                            ])
                        >
                            <span class="block text-[11px] font-bold leading-tight">{{ $g->label() }}</span>
                            <span @class([
                                'mt-0.5 block text-[10px] leading-tight',
                                'text-slate-300' => $this->gayaTerpilih === $g,
                                'text-slate-400' => $this->gayaTerpilih !== $g,
                            ])>{{ $g->keterangan() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <span class="{{ $kelasLabel }}">Aksen</span>
                <div class="flex gap-2">
                    @foreach (Aksen::cases() as $a)
                        <button
                            type="button"
                            wire:click="$set('aksen', '{{ $a->value }}')"
                            title="{{ $a->label() }}"
                            @class([
                                'size-8 rounded-full transition',
                                'ring-2 ring-slate-900 ring-offset-2' => $this->aksenTerpilih === $a,
                                'hover:scale-110' => $this->aksenTerpilih !== $a,
                            ])
                            style="background: {{ $a->heks() }};"
                        ></button>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ---------- Logo ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Logo <span class="text-xs font-medium text-slate-400">— opsional</span></h2>

            @if ($logoUrl)
                <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-2">
                    <img src="{{ $logoUrl }}" alt="" class="h-10 w-auto max-w-24 object-contain">
                    <button type="button" wire:click="hapusLogo" class="ml-auto rounded-md px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-red-100 hover:text-red-600">
                        Hapus
                    </button>
                </div>
            @else
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-3 py-3 text-xs font-semibold text-slate-500 transition hover:border-slate-900 hover:text-slate-900">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    Unggah logo
                    <input type="file" wire:model="berkasLogo" accept="image/png,image/jpeg,image/webp" class="hidden">
                </label>
            @endif

            <div wire:loading wire:target="berkasLogo" class="mt-2 text-xs font-medium text-slate-500">Memproses logo…</div>
            @error('berkasLogo') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
        </section>

        <button
            type="button"
            wire:click="simpan"
            class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700"
        >
            <span wire:loading.remove wire:target="simpan">{{ $thumbnailId ? 'Perbarui' : 'Simpan ke riwayat' }}</span>
            <span wire:loading wire:target="simpan">Menyimpan…</span>
        </button>
    </div>

    {{-- ================= Pratinjau ================= --}}
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-900">Pratinjau</h2>
                <p class="text-xs text-slate-500">
                    Susunan kolase berubah mengikuti bentuk tiap kanvas, bukan sekadar diperkecil.
                </p>
            </div>
            <button
                type="button"
                data-unduh="semua"
                data-nama="{{ $namaDasar }}"
                class="ml-auto shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700 disabled:opacity-50"
            >Unduh semua</button>
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-2">
            @foreach ($this->ukuranSemua as $u)
                <section @class([
                    'rounded-xl border border-slate-200 bg-white p-4',
                    'xl:col-span-2' => $u === \App\Enums\UkuranThumbnail::Facebook,
                ])>
                    <header class="mb-3 flex items-start gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900">{{ $u->label() }}</h3>
                            <p class="text-xs text-slate-500">{{ $u->keterangan() }}</p>
                        </div>
                        <p class="ml-auto shrink-0 text-right text-[11px] font-medium tabular-nums text-slate-400">
                            {{ $u->lebar() }}&times;{{ $u->tinggi() }}<br>{{ $u->rasio() }}
                        </p>
                    </header>

                    <div
                        data-pratinjau
                        class="relative w-full overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200"
                        style="aspect-ratio: {{ $u->lebar() }} / {{ $u->tinggi() }};"
                    >
                        <x-kanvas-thumbnail
                            :ukuran="$u"
                            :judul="$judul"
                            :subjudul="$subjudul"
                            :label="$label"
                            :tanggal="$this->tanggalTampil"
                            :gaya="$this->gayaTerpilih"
                            :model="$this->modelTerpilih"
                            :aksen="$this->aksenTerpilih"
                            :logo="$logoUrl"
                            :foto="$foto"
                        />
                    </div>

                    <button
                        type="button"
                        data-unduh="{{ $u->value }}"
                        data-nama="{{ $namaDasar }}"
                        class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-900 hover:text-slate-900 disabled:opacity-50"
                    >Unduh PNG</button>
                </section>
            @endforeach
        </div>
    </div>
</div>
