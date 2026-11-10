@extends('layouts.public')

@php
    use App\Support\Money;
    use App\Support\Text;

    $images = $tour->images;
    $primary = $images->firstWhere('is_primary', true) ?? $images->first();
    $waUrl = \App\Services\WhatsAppLink::url('Halo, saya tertarik dengan paket wisata '.$tour->title.'. Mohon info jadwal, jumlah peserta, dan harganya.');
    $itinerary = Text::lines($tour->itinerary);
    $included = Text::lines($tour->included_facilities);
    $excluded = Text::lines($tour->excluded_facilities);
    $description = $tour->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($tour->short_description ?: $tour->description)) ?: $tour->title, 155);
@endphp

@section('title', $tour->meta_title ?: $tour->title)
@section('description', $description)
@if ($primary)
    @section('image', $primary->url())
@endif

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-6">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('tours.index') }}" class="hover:underline">Paket Wisata</a>
            <span aria-hidden="true">/</span>
            <span>{{ $tour->title }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-5">
            <div class="lg:col-span-3" x-data="{ current: 0, items: @js($images->map(fn ($i) => ['src' => $i->url(), 'alt' => $i->alt_text ?: $tour->title])->values()) }">
                @if ($images->isNotEmpty())
                    <div class="rounded-xl overflow-hidden border border-line bg-white aspect-[4/3]">
                        <img :src="items[current].src" :alt="items[current].alt" class="size-full object-cover"
                             src="{{ $primary->url() }}" alt="{{ $primary->alt_text ?: $tour->title }}">
                    </div>
                    @if ($images->count() > 1)
                        <ul class="mt-3 grid grid-cols-4 sm:grid-cols-5 gap-2" aria-label="Foto lainnya">
                            @foreach ($images as $index => $image)
                                <li>
                                    <button type="button" @click="current = {{ $index }}"
                                            :aria-current="current === {{ $index }} ? 'true' : 'false'"
                                            :class="current === {{ $index }} ? 'ring-2 ring-plate-ink' : 'opacity-80 hover:opacity-100'"
                                            class="block w-full rounded-lg overflow-hidden aspect-[4/3] border border-line"
                                            aria-label="Tampilkan foto {{ $index + 1 }}">
                                        <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-cover">
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <div class="rounded-xl border border-dashed border-line bg-white aspect-[4/3] grid place-items-center text-road-soft">Foto belum tersedia</div>
                @endif
            </div>

            <div class="lg:col-span-2">
                <h1 class="text-4xl leading-tight">{{ $tour->title }}</h1>
                @if ($tour->duration_label)
                    <p class="mt-2 text-road-soft">{{ $tour->duration_label }}</p>
                @endif
                @if ($tour->short_description)
                    <p class="mt-3 text-road-soft">{{ $tour->short_description }}</p>
                @endif

                <div class="mt-5">
                    @if ($tour->price !== null)
                        <x-plate small class="inline-flex items-baseline gap-1 px-3 py-1.5">
                            <span class="text-sm font-semibold">Mulai</span>
                            <span class="text-2xl">{{ Money::rupiah($tour->price) }}</span>
                        </x-plate>
                        <p class="mt-2 text-sm text-road-soft">Harga final dan jadwal dikonfirmasi admin.</p>
                    @else
                        <p class="font-semibold">Tanyakan harga via WhatsApp</p>
                    @endif
                </div>

                @if ($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa mt-5 w-full">Tanya paket ini lewat WhatsApp</a>
                @endif
            </div>
        </div>

        @if ($tour->description)
            <section class="mt-10 max-w-3xl" aria-labelledby="h-desk">
                <h2 id="h-desk" class="text-2xl mb-2">Tentang paket ini</h2>
                <p class="text-road">{!! nl2br(e($tour->description)) !!}</p>
            </section>
        @endif

        @if ($itinerary)
            <section class="mt-10 max-w-3xl" aria-labelledby="h-itin">
                <h2 id="h-itin" class="text-2xl mb-3">Rencana perjalanan</h2>
                <ol class="bg-white border border-line rounded-xl divide-y divide-line">
                    @foreach ($itinerary as $step)
                        <li class="p-4 flex gap-3">
                            <span class="font-display text-plate-ink bg-plate rounded px-2 h-fit text-sm leading-6" aria-hidden="true">{{ $loop->iteration }}</span>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($included || $excluded)
            <div class="mt-10 grid gap-6 md:grid-cols-2 max-w-3xl">
                @if ($included)
                    <section aria-labelledby="h-inc">
                        <h2 id="h-inc" class="text-2xl mb-3">Sudah termasuk</h2>
                        <ul class="bg-white border border-line rounded-xl divide-y divide-line">
                            @foreach ($included as $item)
                                <li class="p-3 flex gap-2"><span aria-hidden="true" class="text-sign-green font-bold">&#10003;</span><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif
                @if ($excluded)
                    <section aria-labelledby="h-exc">
                        <h2 id="h-exc" class="text-2xl mb-3">Belum termasuk</h2>
                        <ul class="bg-white border border-line rounded-xl divide-y divide-line">
                            @foreach ($excluded as $item)
                                <li class="p-3 flex gap-2"><span aria-hidden="true" class="text-sign-red font-bold">&#10005;</span><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-12" aria-labelledby="h-rel">
                <h2 id="h-rel" class="text-2xl mb-4">Paket lainnya</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <x-tour-card :tour="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([
        \App\Support\StructuredData::tour($tour),
        \App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Paket Wisata', route('tours.index')], [$tour->title, $tour->publicUrl()]]),
    ])" />
@endpush
