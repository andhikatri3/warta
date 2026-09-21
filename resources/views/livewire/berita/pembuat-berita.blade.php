@php
    use App\Enums\NadaBerita;
    use App\Enums\PanjangBerita;

    $kelasLabel = 'mb-1.5 block text-xs font-semibold text-slate-700';
    $kelasInput = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10';
    $kelasGalat = 'mt-1 text-xs font-medium text-red-600';

    $ruas = [
        ['nama' => 'apa', 'label' => 'Apa yang terjadi', 'wajib' => true, 'baris' => 3, 'contoh' => 'Pemerintah desa meresmikan gedung Posyandu baru di dusun Krajan'],
        ['nama' => 'siapa', 'label' => 'Siapa yang terlibat', 'wajib' => true, 'baris' => 2, 'contoh' => 'Kepala Desa Sutrisno, Camat Nguling, kader PKK, dan sekitar 80 warga'],
        ['nama' => 'kapan', 'label' => 'Kapan', 'wajib' => true, 'baris' => 1, 'contoh' => 'Sabtu, 21 September 2026, pukul 08.00'],
        ['nama' => 'diMana', 'label' => 'Di mana', 'wajib' => true, 'baris' => 1, 'contoh' => 'Balai Dusun Krajan, Desa Penunggul'],
        ['nama' => 'mengapa', 'label' => 'Mengapa', 'wajib' => false, 'baris' => 2, 'contoh' => 'Posyandu lama sudah tidak muat menampung peserta'],
        ['nama' => 'bagaimana', 'label' => 'Bagaimana jalannya', 'wajib' => false, 'baris' => 3, 'contoh' => 'Diawali sambutan kepala desa, pemotongan pita, lalu pemeriksaan balita perdana'],
    ];
@endphp

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">

    {{-- ================= Bahan ================= --}}
    <div class="space-y-4">

        @if (session('sukses'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                {{ session('sukses') }}
            </div>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-bold text-slate-900">Bahan berita</h2>
            <p class="mb-3 mt-1 text-xs leading-relaxed text-slate-500">
                Semua fakta dalam berita diambil dari sini. Model tidak mencari data dan tidak
                menebak — apa yang tidak kamu tulis, tidak akan muncul.
            </p>

            @foreach ($ruas as $r)
                <div class="mb-3">
                    <label for="{{ $r['nama'] }}" class="{{ $kelasLabel }}">
                        {{ $r['label'] }}
                        @unless ($r['wajib'])
                            <span class="font-normal text-slate-400">— opsional</span>
                        @endunless
                    </label>
                    @if ($r['baris'] > 1)
                        <textarea
                            id="{{ $r['nama'] }}"
                            rows="{{ $r['baris'] }}"
                            wire:model="{{ $r['nama'] }}"
                            placeholder="{{ $r['contoh'] }}"
                            class="{{ $kelasInput }} resize-none"
                        ></textarea>
                    @else
                        <input
                            id="{{ $r['nama'] }}"
                            type="text"
                            wire:model="{{ $r['nama'] }}"
                            placeholder="{{ $r['contoh'] }}"
                            class="{{ $kelasInput }}"
                        >
                    @endif
                    @error($r['nama']) <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div>
                <label for="catatan" class="{{ $kelasLabel }}">
                    Catatan tambahan <span class="font-normal text-slate-400">— opsional</span>
                </label>
                <textarea
                    id="catatan"
                    rows="2"
                    wire:model="catatan"
                    placeholder="Angka, nama program, atau keterangan lain yang perlu masuk"
                    class="{{ $kelasInput }} resize-none"
                ></textarea>
                @error('catatan') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ---------- Kutipan ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-bold text-slate-900">Kutipan narasumber <span class="text-xs font-medium text-slate-400">— opsional</span></h2>
            <p class="mb-3 mt-1 text-xs leading-relaxed text-slate-500">
                Disalin persis huruf demi huruf. Model hanya menambahkan kalimat pengantar,
                tidak memperhalus dan tidak menyusun ulang kata-katanya.
            </p>

            <div class="mb-3">
                <label for="penuturKutipan" class="{{ $kelasLabel }}">Penutur</label>
                <input
                    id="penuturKutipan"
                    type="text"
                    wire:model="penuturKutipan"
                    placeholder="Kepala Desa Penunggul, Sutrisno"
                    class="{{ $kelasInput }}"
                >
                @error('penuturKutipan') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="kutipan" class="{{ $kelasLabel }}">Kutipan</label>
                <textarea
                    id="kutipan"
                    rows="3"
                    wire:model="kutipan"
                    placeholder="Gedung ini milik warga, rawatlah bersama-sama."
                    class="{{ $kelasInput }} resize-none"
                ></textarea>
                @error('kutipan') <p class="{{ $kelasGalat }}">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ---------- Gaya ---------- --}}
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Gaya dan panjang</h2>

            <div class="mb-4">
                <span class="{{ $kelasLabel }}">Nada</span>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach (NadaBerita::cases() as $n)
                        <button
                            type="button"
                            wire:click="$set('nada', '{{ $n->value }}')"
                            @class([
                                'rounded-lg border px-2 py-2 text-left transition',
                                'border-slate-900 bg-slate-900 text-white' => $nada === $n->value,
                                'border-slate-200 text-slate-700 hover:border-slate-400' => $nada !== $n->value,
                            ])
                        >
                            <span class="block text-[11px] font-bold leading-tight">{{ $n->label() }}</span>
                            <span @class([
                                'mt-0.5 block text-[10px] leading-tight',
                                'text-slate-300' => $nada === $n->value,
                                'text-slate-400' => $nada !== $n->value,
                            ])>{{ $n->keterangan() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <span class="{{ $kelasLabel }}">Panjang</span>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach (PanjangBerita::cases() as $p)
                        <button
                            type="button"
                            wire:click="$set('panjang', '{{ $p->value }}')"
                            @class([
                                'rounded-lg border px-2 py-2 text-left transition',
                                'border-slate-900 bg-slate-900 text-white' => $panjang === $p->value,
                                'border-slate-200 text-slate-700 hover:border-slate-400' => $panjang !== $p->value,
                            ])
                        >
                            <span class="block text-[11px] font-bold leading-tight">{{ $p->label() }}</span>
                            <span @class([
                                'mt-0.5 block text-[10px] leading-tight',
                                'text-slate-300' => $panjang === $p->value,
                                'text-slate-400' => $panjang !== $p->value,
                            ])>{{ $p->kisaran() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        <button
            type="button"
            wire:click="tulis"
            wire:loading.attr="disabled"
            wire:target="tulis"
            class="w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-700 disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="tulis">{{ $sudahMenulis ? 'Tulis ulang' : 'Tulis berita' }}</span>
            <span wire:loading wire:target="tulis">Sedang menulis, mohon tunggu…</span>
        </button>

        @error('naskah')
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- ================= Naskah ================= --}}
    <div class="space-y-4">
        @if (! $sudahMenulis)
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center">
                <p class="text-sm font-semibold text-slate-700">Naskah akan muncul di sini</p>
                <p class="mx-auto mt-1 max-w-md text-sm leading-relaxed text-slate-500">
                    Isi bahannya di sebelah kiri, lalu tekan “Tulis berita”. Menulis satu berita
                    biasanya memakan belasan sampai puluhan detik.
                </p>
            </div>
        @else
            {{-- ---------- Pilihan judul ---------- --}}
            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-bold text-slate-900">Pilihan judul</h2>
                <p class="mb-3 mt-1 text-xs text-slate-500">
                    Tiga sudut berbeda. Yang dipilih ikut tersimpan, dua sisanya tetap disimpan
                    sebagai cadangan.
                </p>

                <div class="space-y-1.5">
                    @foreach ($judulPilihan as $i => $j)
                        <button
                            type="button"
                            wire:click="pilihJudul({{ $i }})"
                            @class([
                                'flex w-full items-start gap-2.5 rounded-lg border px-3 py-2.5 text-left transition',
                                'border-slate-900 bg-slate-50' => $judul === $j,
                                'border-slate-200 hover:border-slate-400' => $judul !== $j,
                            ])
                        >
                            <span @class([
                                'mt-0.5 grid size-4 shrink-0 place-items-center rounded-full border-2',
                                'border-slate-900 bg-slate-900' => $judul === $j,
                                'border-slate-300' => $judul !== $j,
                            ])>
                                @if ($judul === $j)
                                    <span class="size-1.5 rounded-full bg-white"></span>
                                @endif
                            </span>
                            <span class="text-sm font-semibold leading-snug text-slate-900">{{ $j }}</span>
                            <span class="ml-auto shrink-0 text-[11px] tabular-nums text-slate-400">{{ mb_strlen($j) }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- ---------- Naskah ---------- --}}
            <section class="rounded-xl border border-slate-200 bg-white">
                <header class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Naskah</h2>
                        <p class="text-xs text-slate-500">{{ $this->jumlahKata }} kata &middot; {{ count($this->paragraf) + 1 }} paragraf</p>
                    </div>
                    <div class="ml-auto flex flex-wrap gap-2">
                        <a
                            href="{{ route('thumbnail.baru', ['judul' => $judul]) }}"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                        >Buat thumbnail</a>
                        <button
                            type="button"
                            wire:click="simpan"
                            class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-700"
                        >
                            <span wire:loading.remove wire:target="simpan">{{ $beritaId ? 'Perbarui' : 'Simpan' }}</span>
                            <span wire:loading wire:target="simpan">Menyimpan…</span>
                        </button>
                    </div>
                </header>

                <article class="px-5 py-5">
                    <h1 class="text-2xl font-extrabold leading-tight tracking-tight text-slate-900">{{ $judul }}</h1>

                    <p class="mt-4 text-[15px] font-semibold leading-relaxed text-slate-800">{{ $lead }}</p>

                    @foreach ($this->paragraf as $p)
                        <p class="mt-3 text-[15px] leading-relaxed text-slate-700">{{ $p }}</p>
                    @endforeach
                </article>

                <footer class="space-y-3 border-t border-slate-200 px-4 py-3">
                    <div>
                        <span class="{{ $kelasLabel }}">Meta description</span>
                        <textarea wire:model="meta" rows="2" class="{{ $kelasInput }} resize-none text-xs"></textarea>
                        <p class="mt-1 text-[11px] text-slate-400">{{ mb_strlen($meta) }} karakter &middot; sebaiknya di bawah 155</p>
                    </div>
                    <div>
                        <span class="{{ $kelasLabel }}">Slug</span>
                        <input type="text" wire:model="slug" class="{{ $kelasInput }} text-xs">
                    </div>
                </footer>
            </section>

            {{-- ---------- Sunting mentah ---------- --}}
            <details class="rounded-xl border border-slate-200 bg-white">
                <summary class="cursor-pointer px-4 py-3 text-sm font-bold text-slate-900">
                    Sunting naskah
                </summary>
                <div class="space-y-3 border-t border-slate-200 px-4 py-3">
                    <div>
                        <span class="{{ $kelasLabel }}">Judul terpilih</span>
                        <input type="text" wire:model.live.debounce.400ms="judul" class="{{ $kelasInput }}">
                    </div>
                    <div>
                        <span class="{{ $kelasLabel }}">Lead</span>
                        <textarea wire:model.live.debounce.400ms="lead" rows="3" class="{{ $kelasInput }} resize-none"></textarea>
                    </div>
                    <div>
                        <span class="{{ $kelasLabel }}">Badan berita</span>
                        <textarea wire:model.live.debounce.400ms="isi" rows="14" class="{{ $kelasInput }} resize-none font-mono text-xs leading-relaxed"></textarea>
                        <p class="mt-1 text-[11px] text-slate-400">Antarparagraf dipisah satu baris kosong.</p>
                    </div>
                </div>
            </details>
        @endif
    </div>
</div>
