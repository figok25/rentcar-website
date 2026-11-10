@extends('layouts.public')

@php
    use App\Support\Text;

    $waUrl = \App\Services\WhatsAppLink::url('Halo, saya ingin menanyakan layanan travel '.$service->title.($service->route_label ? ' ('.$service->route_label.')' : '').'. Mohon info jadwal dan harganya.');
    $terms = Text::lines($service->terms);
    $description = $service->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($service->short_description ?: $service->description)) ?: $service->title, 155);
@endphp

@section('title', $service->meta_title ?: $service->title)
@section('description', $description)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('travel.index') }}" class="hover:underline">Travel</a>
            <span aria-hidden="true">/</span>
            <span>{{ $service->title }}</span>
        </nav>

        <h1 class="text-4xl md:text-5xl leading-tight">{{ $service->title }}</h1>
        @if ($service->route_label)
            <p class="mt-2 text-lg text-road-soft">{{ $service->route_label }}</p>
        @endif
        @if ($service->short_description)
            <p class="mt-3 text-road-soft max-w-prose">{{ $service->short_description }}</p>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-4">
            @if ($service->price_label)
                <x-plate small class="px-3 py-1.5 text-xl">{{ $service->price_label }}</x-plate>
            @else
                <p class="font-semibold">Tanyakan harga via WhatsApp</p>
            @endif
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa">Tanya lewat WhatsApp</a>
            @endif
        </div>

        @if ($service->description)
            <section class="mt-10" aria-labelledby="h-desk">
                <h2 id="h-desk" class="text-2xl mb-2">Tentang layanan ini</h2>
                <p class="text-road">{!! nl2br(e($service->description)) !!}</p>
            </section>
        @endif

        @if ($service->vehicle_information)
            <section class="mt-8" aria-labelledby="h-armada">
                <h2 id="h-armada" class="text-2xl mb-2">Armada</h2>
                <p class="bg-white border border-line rounded-xl p-4">{!! nl2br(e($service->vehicle_information)) !!}</p>
            </section>
        @endif

        @if ($terms)
            <section class="mt-8" aria-labelledby="h-syarat">
                <h2 id="h-syarat" class="text-2xl mb-3">Syarat dan ketentuan</h2>
                <ul class="bg-white border border-line rounded-xl divide-y divide-line">
                    @foreach ($terms as $term)
                        <li class="p-4 flex gap-3">
                            <span aria-hidden="true" class="mt-2 size-2 rounded-full bg-plate shrink-0 ring-1 ring-plate-ink"></span>
                            <span>{{ $term }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-12" aria-labelledby="h-rel">
                <h2 id="h-rel" class="text-2xl mb-4">Layanan lainnya</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <x-travel-card :service="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([
        \App\Support\StructuredData::travel($service),
        \App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Travel', route('travel.index')], [$service->title, $service->publicUrl()]]),
    ])" />
@endpush
