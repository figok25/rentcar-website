@props(['site'])
@php
    $nav = [
        ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Rental Mobil', 'href' => route('vehicles.index'), 'active' => request()->routeIs('vehicles.*')],
    ];
    if ($site['showMotor']) {
        $nav[] = ['label' => 'Rental Motor', 'href' => route('motor.index'), 'active' => request()->routeIs('motor.*')];
    }
    $nav[] = ['label' => 'Syarat Sewa', 'href' => route('requirements'), 'active' => request()->routeIs('requirements')];
    $nav[] = ['label' => 'Kontak', 'href' => route('contact'), 'active' => request()->routeIs('contact')];
@endphp
<header class="bg-white border-b border-line sticky top-0 z-40" x-data="{ open: false }" @keydown.escape.window="open = false">
    <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" aria-label="{{ $site['name'] }} - beranda">
            @if ($site['logo'])
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($site['logo']) }}" alt="{{ $site['name'] }}" class="h-9 w-auto">
            @else
                <x-plate small class="px-3 py-1 text-lg leading-none">{{ $site['name'] }}</x-plate>
            @endif
        </a>

        <nav class="hidden md:flex items-center gap-1" aria-label="Menu utama">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}"
                   @if ($item['active']) aria-current="page" @endif
                   class="px-3 py-2 rounded-md font-medium {{ $item['active'] ? 'bg-paper text-plate-ink' : 'text-road-soft hover:text-plate-ink' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if ($site['whatsapp'])
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa hidden sm:inline-flex">Chat WhatsApp</a>
            @endif
            <button type="button" class="md:hidden btn btn-outline px-3" @click="open = !open"
                    :aria-expanded="open.toString()" aria-controls="menu-mobile" aria-label="Buka menu">
                <span x-show="!open">Menu</span>
                <span x-show="open" x-cloak>Tutup</span>
            </button>
        </div>
    </div>

    <nav id="menu-mobile" class="md:hidden border-t border-line bg-white" x-show="open" x-cloak aria-label="Menu seluler">
        <div class="mx-auto max-w-6xl px-4 py-2 flex flex-col">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif
                   class="py-3 font-medium border-b border-line last:border-0 {{ $item['active'] ? 'text-plate-ink' : 'text-road-soft' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            @if ($site['whatsapp'])
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa my-3">Chat WhatsApp</a>
            @endif
        </div>
    </nav>
</header>
