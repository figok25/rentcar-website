@props(['vehicle', 'whatsapp' => null, 'heading' => 'h3'])
@php
    $url = $vehicle->publicUrl();
    $image = $vehicle->primaryImage;
    $message = 'Halo, saya tertarik dengan '.$vehicle->name.'. Mohon info ketersediaan dan harganya.';
    $waUrl = \App\Services\WhatsAppLink::url($message);
@endphp
<article class="card card-hover overflow-hidden flex flex-col">
    {{-- Foto unit. Lencana di atas foto: status unit di kanan, "Populer" (unit unggulan) di kiri. --}}
    <div class="relative aspect-[4/3] bg-white">
        <a href="{{ $url }}" class="block size-full" tabindex="-1" aria-hidden="true">
            @if ($image)
                <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-contain p-3">
            @else
                <span class="absolute inset-0 grid place-items-center text-sm text-road-soft">Foto belum tersedia</span>
            @endif
        </a>
        <x-status-badge :status="$vehicle->status" class="absolute right-3 top-3 shadow-sm" />
        @if ($vehicle->is_featured)
            <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-brand px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                <svg viewBox="0 0 20 20" class="size-3 text-accent-light" fill="currentColor" aria-hidden="true"><path d="m10 1.8 2.5 5.1 5.6.8-4.1 3.9 1 5.6L10 14.6l-5 2.6 1-5.6-4.1-3.9 5.6-.8Z"/></svg>
                Populer
            </span>
        @endif
    </div>

    <div class="p-4 flex-1 flex flex-col gap-3">
        <div>
            <{{ $heading }} class="text-lg leading-snug">
                <a href="{{ $url }}" class="hover:text-brand">{{ $vehicle->name }}</a>
            </{{ $heading }}>
            <p class="text-sm text-road-soft mt-0.5">
                {{ collect([$vehicle->brand, $vehicle->category?->name])->filter()->implode(', ') }}
            </p>
        </div>

        <ul class="flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-road-soft">
            <li class="inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" class="size-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1M7.7 16.3l-2.1 2.1"/></svg>
                {{ $vehicle->transmission->label() }}
            </li>
            <li class="inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" class="size-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3s6 6.2 6 10.5a6 6 0 0 1-12 0C6 9.2 12 3 12 3Z"/></svg>
                {{ $vehicle->fuel_type->label() }}
            </li>
            <li class="inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" class="size-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
                {{ $vehicle->passenger_capacity }} kursi
            </li>
        </ul>

        <div class="mt-auto pt-1">
            @if ($vehicle->starting_price !== null)
                <p class="leading-none">
                    <span class="block text-xs font-semibold text-road-soft mb-1">Mulai dari</span>
                    <span class="text-xl font-extrabold text-accent">{{ \App\Support\Money::rupiah((int) $vehicle->starting_price) }}</span>
                    <span class="text-sm text-road-soft">/hari</span>
                </p>
            @else
                <p class="text-sm font-bold text-ink">Tanyakan harga via WhatsApp</p>
            @endif
        </div>

        <div class="flex gap-2">
            <a href="{{ $url }}" class="btn btn-outline btn-sm flex-1">Lihat detail</a>
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm px-3.5"
                   aria-label="Konsultasi {{ $vehicle->name }} lewat WhatsApp" title="Konsultasi lewat WhatsApp">
                    <span class="sr-only">Konsultasi</span>
                </a>
            @endif
        </div>
    </div>
</article>
