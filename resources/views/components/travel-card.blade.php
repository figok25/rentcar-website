@props(['service', 'heading' => 'h3'])
@php
    $waUrl = \App\Services\WhatsAppLink::url('Halo, saya ingin menanyakan layanan travel '.$service->title.($service->route_label ? ' ('.$service->route_label.')' : '').'. Mohon info jadwal dan harganya.');
@endphp
<article class="card p-5 flex flex-col gap-3">
    <div>
        <{{ $heading }} class="text-xl leading-tight"><a href="{{ $service->publicUrl() }}" class="hover:underline">{{ $service->title }}</a></{{ $heading }}>
        @if ($service->route_label)
            <p class="text-sm text-road-soft mt-1">{{ $service->route_label }}</p>
        @endif
    </div>
    @if ($service->short_description)
        <p class="text-sm text-road-soft">{{ \Illuminate\Support\Str::limit($service->short_description, 120) }}</p>
    @endif
    <div class="mt-auto pt-2">
        @if ($service->price_label)
            <x-plate small class="inline-block px-3 py-1 text-base">{{ $service->price_label }}</x-plate>
        @else
            <p class="text-sm font-semibold">Tanyakan harga via WhatsApp</p>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ $service->publicUrl() }}" class="btn btn-outline flex-1 text-sm">Lihat detail</a>
        @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa flex-1 text-sm"
               aria-label="Tanya {{ $service->title }} lewat WhatsApp">Konsultasi</a>
        @endif
    </div>
</article>
