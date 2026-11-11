@props(['site'])
@php
    // Tiap item menu boleh punya sub-menu ('children'); item tanpa anak berupa tautan biasa.
    $carChildren = collect([['label' => 'Semua mobil', 'href' => route('vehicles.index')]])
        ->merge(collect($site['navCategories'])->map(fn ($c) => ['label' => $c->name, 'href' => route('vehicles.index', ['category' => $c->slug])]))
        ->all();

    $nav = [
        ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home')],
        [
            'label' => 'Rental Mobil',
            'href' => route('vehicles.index'),
            'active' => request()->routeIs('vehicles.*'),
            'children' => count($carChildren) > 2 ? $carChildren : [],
        ],
    ];
    if ($site['showMotor']) {
        $nav[] = ['label' => 'Rental Motor', 'href' => route('motor.index'), 'active' => request()->routeIs('motor.*')];
    }
    if ($site['showTours'] && $site['showTravel']) {
        $nav[] = [
            'label' => 'Wisata & Travel',
            'href' => route('tours.index'),
            'active' => request()->routeIs('tours.*') || request()->routeIs('travel.*'),
            'children' => [
                ['label' => 'Paket Wisata', 'href' => route('tours.index')],
                ['label' => 'Travel', 'href' => route('travel.index')],
            ],
        ];
    } elseif ($site['showTours']) {
        $nav[] = ['label' => 'Paket Wisata', 'href' => route('tours.index'), 'active' => request()->routeIs('tours.*')];
    } elseif ($site['showTravel']) {
        $nav[] = ['label' => 'Travel', 'href' => route('travel.index'), 'active' => request()->routeIs('travel.*')];
    }
    $nav[] = ['label' => 'Syarat Sewa', 'href' => route('requirements'), 'active' => request()->routeIs('requirements')];
    $nav[] = ['label' => 'Kontak', 'href' => route('contact'), 'active' => request()->routeIs('contact')];
    // Menu panjang baru tampil mendatar di layar lebih lebar.
    $wide = count($nav) > 6;
    $desktopNav = $wide ? 'hidden xl:flex' : 'hidden lg:flex';
    $mobileOnly = $wide ? 'xl:hidden' : 'lg:hidden';
    $linkBase = 'px-2.5 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors';
@endphp
<header class="bg-white/90 backdrop-blur border-b border-line sticky top-0 z-40" x-data="{ open: false, sub: null }"
        @keydown.escape.window="open = false; sub = null" @click.outside="sub = null">
    <div class="container-page h-[4.25rem] flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" aria-label="{{ $site['name'] }} - beranda">
            @if ($site['logo'])
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($site['logo']) }}" alt="{{ $site['name'] }}" class="h-9 w-auto">
            @else
                <x-plate small class="px-3 py-1 text-xl leading-none tracking-wide uppercase">{{ $site['name'] }}</x-plate>
            @endif
        </a>

        <nav class="{{ $desktopNav }} items-center gap-1" aria-label="Menu utama">
            @foreach ($nav as $i => $item)
                @if (! empty($item['children']))
                    <div class="relative">
                        <button type="button" @click="sub = sub === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="(sub === {{ $i }}).toString()" aria-haspopup="true"
                                class="{{ $linkBase }} inline-flex items-center gap-1 {{ $item['active'] ? 'bg-paper text-plate-ink' : 'text-road-soft hover:text-plate-ink' }}">
                            {{ $item['label'] }}
                            <svg viewBox="0 0 20 20" class="size-4 transition-transform" :class="sub === {{ $i }} && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 7.5 5 5 5-5"/></svg>
                        </button>
                        <div x-show="sub === {{ $i }}" x-cloak x-transition.opacity.duration.100ms
                             class="absolute left-0 top-full mt-2 min-w-52 card p-1.5 shadow-lift">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['href'] }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-road hover:bg-paper hover:text-plate-ink">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item['href'] }}"
                       @if ($item['active']) aria-current="page" @endif
                       class="{{ $linkBase }} {{ $item['active'] ? 'bg-paper text-plate-ink' : 'text-road-soft hover:text-plate-ink' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if ($site['whatsapp'])
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm hidden sm:inline-flex whitespace-nowrap">Pesan via WhatsApp</a>
            @endif
            <button type="button" class="{{ $mobileOnly }} btn btn-outline px-3" @click="open = !open"
                    :aria-expanded="open.toString()" aria-controls="menu-mobile" aria-label="Buka menu">
                <span x-show="!open">Menu</span>
                <span x-show="open" x-cloak>Tutup</span>
            </button>
        </div>
    </div>

    <nav id="menu-mobile" class="{{ $mobileOnly }} border-t border-line bg-white" x-show="open" x-cloak aria-label="Menu seluler">
        <div class="container-page py-2 flex flex-col">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif
                   class="py-3 font-semibold {{ empty($item['children']) ? 'border-b border-line last:border-0' : '' }} {{ $item['active'] ? 'text-plate-ink' : 'text-road-soft' }}">
                    {{ $item['label'] }}
                </a>
                @if (! empty($item['children']))
                    <div class="mb-2 ml-3 border-l-2 border-plate pl-4 flex flex-col border-b border-line pb-2">
                        @foreach ($item['children'] as $child)
                            <a href="{{ $child['href'] }}" class="py-2 text-sm font-medium text-road-soft">{{ $child['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            @endforeach
            @if ($site['whatsapp'])
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa my-3">Pesan via WhatsApp</a>
            @endif
        </div>
    </nav>
</header>
