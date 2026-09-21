<div class="space-y-4">

    @if (session('sukses'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
            {{ session('sukses') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900">Arsip berita</h1>
            <p class="text-sm text-slate-500">
                Bahan 5W+1H ikut tersimpan di tiap berita, jadi naskahnya bisa ditulis ulang
                dengan gaya lain tanpa mengetik ulang fakta-faktanya.
            </p>
        </div>
        <a href="{{ route('berita.baru') }}" class="ml-auto shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
            Tulis baru
        </a>
    </div>

    <input
        type="search"
        wire:model.live.debounce.400ms="cari"
        placeholder="Cari judul atau lead…"
        class="w-full max-w-sm rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition placeholder:text-slate-400 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
    >

    @if ($daftar->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-semibold text-slate-700">
                {{ $cari !== '' ? 'Tidak ada berita yang cocok' : 'Belum ada berita tersimpan' }}
            </p>
            <p class="mt-1 text-sm text-slate-500">
                {{ $cari !== '' ? 'Coba kata kunci lain.' : 'Tulis satu, lalu tekan “Simpan”.' }}
            </p>
        </div>
    @else
        <div class="divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white">
            @foreach ($daftar as $item)
                <article class="flex items-start gap-4 p-4">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-bold leading-snug text-slate-900">{{ $item->judul }}</h2>
                        <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-slate-600">{{ $item->lead }}</p>
                        <p class="mt-1.5 text-[11px] text-slate-400">
                            {{ $item->nada->label() }} &middot; {{ $item->jumlahKata() }} kata &middot;
                            {{ $item->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-col gap-1.5">
                        <a
                            href="{{ route('berita.sunting', $item) }}"
                            class="rounded-md border border-slate-300 px-2.5 py-1.5 text-center text-xs font-bold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                        >Buka</a>
                        <a
                            href="{{ route('thumbnail.baru', ['judul' => $item->judul]) }}"
                            class="rounded-md border border-slate-300 px-2.5 py-1.5 text-center text-xs font-bold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                        >Thumbnail</a>
                        <button
                            type="button"
                            wire:click="hapus({{ $item->id }})"
                            wire:confirm="Hapus berita ini?"
                            class="rounded-md px-2.5 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-red-100 hover:text-red-600"
                        >Hapus</button>
                    </div>
                </article>
            @endforeach
        </div>

        <div>{{ $daftar->links() }}</div>
    @endif
</div>
