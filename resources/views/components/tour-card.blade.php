@props(['tour', 'heading' => 'h3'])
@php
    $image = $tour->primaryImage;
    $waUrl = \App\Services\WhatsAppLink::url('Halo, saya tertarik dengan paket wisata '.$tour->title.'. Mohon info jadwal, jumlah peserta, dan harganya.');
@endphp
<article class="card card-hover overflow-hidden flex flex-col">
    <a href="{{ $tour->publicUrl() }}" class="relative block aspect-[4/3] bg-paper" tabindex="-1" aria-hidden="true">
        @if ($image)
            <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-cover">
        @else
            <span class="absolute inset-0 grid place-items-center text-sm text-road-soft">Foto belum tersedia</span>
        @endif
    </a>
    <div class="p-4 flex-1 flex flex-col gap-3">
        <div>
            <{{ $heading }} class="text-xl leading-tight"><a href="{{ $tour->publicUrl() }}" class="hover:underline">{{ $tour->title }}</a></{{ $heading }}>
            @if ($tour->duration_label)
                <p class="text-sm text-road-soft mt-1">{{ $tour->duration_label }}</p>
            @endif
        </div>
        @if ($tour->short_description)
            <p class="text-sm text-road-soft">{{ \Illuminate\Support\Str::limit($tour->short_description, 110) }}</p>
        @endif
        <div class="mt-auto pt-2">
            @if ($tour->price !== null)
                <x-plate small class="inline-flex items-baseline gap-1 px-3 py-1">
                    <span class="text-sm font-semibold">Mulai</span>
                    <span class="text-lg">{{ \App\Support\Money::rupiah($tour->price) }}</span>
                </x-plate>
            @else
                <p class="text-sm font-semibold">Tanyakan harga via WhatsApp</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ $tour->publicUrl() }}" class="btn btn-outline flex-1 text-sm">Lihat detail</a>
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa flex-1 text-sm"
                   aria-label="Konsultasi paket {{ $tour->title }} lewat WhatsApp">Konsultasi</a>
            @endif
        </div>
    </div>
</article>
