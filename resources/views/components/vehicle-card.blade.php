@props(['vehicle', 'whatsapp' => null, 'heading' => 'h3'])
@php
    $url = $vehicle->publicUrl();
    $image = $vehicle->primaryImage;
    $message = 'Halo, saya tertarik dengan '.$vehicle->name.'. Mohon info ketersediaan dan harganya.';
    $waUrl = \App\Services\WhatsAppLink::url($message);
@endphp
<article class="bg-white rounded-xl border border-line overflow-hidden flex flex-col">
    <a href="{{ $url }}" class="relative block aspect-[4/3] bg-paper" tabindex="-1" aria-hidden="true">
        @if ($image)
            <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-cover">
        @else
            <span class="absolute inset-0 grid place-items-center text-sm text-road-soft">Foto belum tersedia</span>
        @endif
    </a>

    <div class="p-4 flex-1 flex flex-col gap-3">
        <div class="flex items-start justify-between gap-2">
            <x-status-badge :status="$vehicle->status" />
            @if ($vehicle->is_featured)
                <span class="text-xs font-semibold text-road-soft">Unggulan</span>
            @endif
        </div>

        <div>
            <{{ $heading }} class="text-xl leading-tight">
                <a href="{{ $url }}" class="hover:underline">{{ $vehicle->name }}</a>
            </{{ $heading }}>
            <p class="text-sm text-road-soft mt-1">
                {{ collect([$vehicle->brand, $vehicle->category?->name])->filter()->implode(', ') }}
            </p>
        </div>

        <ul class="text-sm text-road-soft flex flex-wrap gap-x-3 gap-y-1">
            <li>{{ $vehicle->transmission->label() }}</li>
            <li>{{ $vehicle->fuel_type->label() }}</li>
            <li>{{ $vehicle->passenger_capacity }} penumpang</li>
        </ul>

        <div class="mt-auto pt-2">
            @if ($vehicle->starting_price !== null)
                <x-plate small class="inline-flex items-baseline gap-1 px-3 py-1">
                    <span class="text-sm font-semibold">Mulai</span>
                    <span class="text-lg">{{ \App\Support\Money::rupiah((int) $vehicle->starting_price) }}</span>
                    <span class="text-sm font-semibold">/hari</span>
                </x-plate>
            @else
                <p class="text-sm font-semibold">Tanyakan harga via WhatsApp</p>
            @endif
        </div>

        <div class="flex gap-2">
            <a href="{{ $url }}" class="btn btn-outline flex-1 text-sm">Lihat detail</a>
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa flex-1 text-sm"
                   aria-label="Konsultasi {{ $vehicle->name }} lewat WhatsApp">Konsultasi</a>
            @endif
        </div>
    </div>
</article>
