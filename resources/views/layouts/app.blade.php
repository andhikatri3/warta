<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Warta' }} &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex h-full flex-col bg-slate-50 text-slate-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-[1600px] items-center gap-6 px-4 sm:px-6">
            <a href="{{ route('thumbnail.baru') }}" class="flex items-center gap-2 font-bold tracking-tight">
                <span class="grid size-7 place-items-center rounded-lg bg-slate-900 text-xs font-black text-white">W</span>
                {{ config('app.name') }}
            </a>

            <nav class="flex items-center gap-1 text-sm font-medium">
                @php
                    $tautan = [
                        ['rute' => 'thumbnail.baru', 'teks' => 'Thumbnail'],
                        ['rute' => 'thumbnail.riwayat', 'teks' => 'Riwayat'],
                    ];
                @endphp
                @foreach ($tautan as $t)
                    <a
                        href="{{ route($t['rute']) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 transition',
                            'bg-slate-900 text-white' => request()->routeIs($t['rute']),
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($t['rute']),
                        ])
                    >{{ $t['teks'] }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1600px] flex-1 px-4 py-6 sm:px-6">
        {{ $slot }}
    </main>
</body>

</html>
