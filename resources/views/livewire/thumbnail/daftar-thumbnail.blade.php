@php
    use App\Enums\UkuranThumbnail;
@endphp

<div class="space-y-4">

    @if (session('sukses'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
            {{ session('sukses') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900">Riwayat</h1>
            <p class="text-sm text-slate-500">
                Yang tersimpan adalah bahannya — teks, foto, dan titik fokus — jadi tiap kartu di bawah
                adalah kanvas yang dirender ulang sekarang, bukan gambar lama yang diawetkan.
            </p>
        </div>
        <a href="{{ route('thumbnail.baru') }}" class="ml-auto shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
            Buat baru
        </a>
    </div>

    @if ($daftar->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-semibold text-slate-700">Belum ada thumbnail tersimpan</p>
            <p class="mt-1 text-sm text-slate-500">Buat satu, lalu tekan “Simpan ke riwayat”.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($daftar as $item)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div
                        data-pratinjau
                        class="relative w-full overflow-hidden bg-slate-100"
                        style="aspect-ratio: {{ UkuranThumbnail::Facebook->lebar() }} / {{ UkuranThumbnail::Facebook->tinggi() }};"
                    >
                        <x-kanvas-thumbnail
                            :ukuran="UkuranThumbnail::Facebook"
                            :judul="$item->judul"
                            :subjudul="$item->subjudul"
                            :gaya="$item->gaya"
                            :model="$item->model_teks"
                            :aksen="$item->aksen"
                            :logo="$item->logoUrl()"
                            :foto="$item->foto->map(fn ($f) => [
                                'url' => $f->url(),
                                'fokus_x' => $f->fokus_x,
                                'fokus_y' => $f->fokus_y,
                            ])->all()"
                        />
                    </div>

                    <div class="flex items-center gap-2 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $item->judul }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $item->foto->count() }} foto &middot; {{ $item->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <a
                            href="{{ route('thumbnail.sunting', $item) }}"
                            class="shrink-0 rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                        >Sunting</a>
                        <button
                            type="button"
                            wire:click="hapus({{ $item->id }})"
                            wire:confirm="Hapus thumbnail ini beserta fotonya?"
                            class="shrink-0 rounded-md px-2 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-red-100 hover:text-red-600"
                        >Hapus</button>
                    </div>
                </article>
            @endforeach
        </div>

        <div>{{ $daftar->links() }}</div>
    @endif
</div>
