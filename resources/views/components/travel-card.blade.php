@props(['service', 'heading' => 'h3'])
@php
    $waUrl = \App\Services\WhatsAppLink::url('Halo, saya ingin menanyakan layanan travel '.$service->title.($service->route_label ? ' ('.$service->route_label.')' : '').'. Mohon info jadwal dan harganya.');
@endphp
<article class="card p-5 flex flex-col gap-3">
    <div>
        <{{ $heading }} class="text-xl leading-tight"><a href="{{ $service->publicUrl() }}" class="hover:text-brand">{{ $service->title }}</a></{{ $heading }}>
        @if ($service->route_label)
            <p class="text-sm text-road-soft mt-1">{{ $service->route_label }}</p>
        @endif
    </div>
    @if ($service->short_description)
        <p class="text-sm text-road-soft">{{ \Illuminate\Support\Str::limit($service->short_description, 120) }}</p>
    @endif
    <div class="mt-auto pt-2">
        @if ($service->price_label)
            <p class="text-lg font-extrabold text-accent leading-tight">{{ $service->price_label }}</p>
        @else
            <p class="text-sm font-bold text-ink">Tanyakan harga via WhatsApp</p>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ $service->publicUrl() }}" class="btn btn-outline btn-sm flex-1">Lihat detail</a>
        @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm flex-1"
               aria-label="Tanya {{ $service->title }} lewat WhatsApp">Konsultasi</a>
        @endif
    </div>
</article>
