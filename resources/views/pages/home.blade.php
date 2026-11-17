@extends('layouts.public')

@section('title', 'Sewa Kendaraan')
@section('description', \Illuminate\Support\Str::limit($heroSubtitle ?: 'Rental kendaraan dengan unit terawat dan harga jelas. Cek unit, lalu konsultasi lewat WhatsApp.', 155))
@if ($heroImage)
    @section('image', $heroImage)
@endif

@php
    // Judul hero: kata terakhir diberi warna merah agar ada titik tekan.
    $titleWords = preg_split('/\s+/', trim($heroTitle ?: 'Sewa Kendaraan Mudah dan Terpercaya')) ?: [];
    $titleLast = count($titleWords) > 1 ? array_pop($titleWords) : null;
    $titleLead = implode(' ', $titleWords);

    $statIcons = [
        '<path d="M3 13l1.6-4.8A2 2 0 0 1 6.5 7h11a2 2 0 0 1 1.9 1.2L21 13M3 13v4h2m16-4v4h-2M3 13h18M7 17h10"/><circle cx="7.5" cy="15.5" r=".9"/><circle cx="16.5" cy="15.5" r=".9"/>',
        '<path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6M20 19v-1a3 3 0 0 0-2.2-2.9M15 5.2a3 3 0 0 1 0 5.6"/>',
        '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9Z"/>',
        '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];
@endphp

@section('content')
    {{-- Hero --}}
    <section class="relative bg-white overflow-hidden">
        <div class="hero-stripes" aria-hidden="true"></div>

        <div class="container-page relative pt-10 pb-24 md:pt-14 md:pb-28 grid gap-10 lg:grid-cols-[1.05fr_1fr] lg:items-center">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-brand-soft px-3.5 py-1.5 text-sm font-semibold text-brand">
                    <span class="size-2 rounded-full bg-accent" aria-hidden="true"></span>
                    {{ $site['showMotor'] ? 'Rental Mobil & Motor' : 'Rental Mobil' }}
                </p>

                <h1 class="mt-4 text-4xl sm:text-5xl md:text-[3.5rem] leading-[1.06] text-ink">
                    {{ $titleLead }}@if ($titleLast) <span class="text-accent">{{ $titleLast }}</span>@endif
                </h1>

                @if ($heroSubtitle)
                    <p class="mt-5 text-lg text-road-soft max-w-prose">{{ $heroSubtitle }}</p>
                @endif

                @if ($advantages->isNotEmpty())
                    <ul class="mt-7 flex flex-wrap gap-x-7 gap-y-3 text-sm font-semibold text-ink">
                        @foreach ($advantages->take(3) as $advantage)
                            <li class="flex items-center gap-2.5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-soft text-brand" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" class="size-4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 10.5 3.5 3.5 7.5-8"/></svg>
                                </span>
                                {{ $advantage->title }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary px-7">
                        Lihat armada
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
                    </a>
                    @if ($site['whatsapp'])
                        <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-outline wa-icon px-7">Chat WhatsApp</a>
                    @endif
                </div>
            </div>

            <div class="relative">
                <div class="rounded-[1.75rem] overflow-hidden bg-brand-soft aspect-[4/3] shadow-lift ring-1 ring-line">
                    <img src="{{ $heroImage }}" alt="Unit kendaraan {{ $site['name'] }}" class="size-full object-cover"
                         fetchpriority="high" decoding="async">
                </div>

                @if (count($stats) > 0)
                    <dl class="relative z-10 -mt-9 mx-4 sm:mx-8 grid grid-cols-2 sm:grid-flow-col sm:auto-cols-fr rounded-2xl bg-brand text-white shadow-lift divide-white/15 sm:divide-x">
                        @foreach ($stats as $i => $stat)
                            <div class="flex items-center gap-3 px-4 py-3.5">
                                <svg viewBox="0 0 24 24" class="size-6 shrink-0 text-accent-light" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $statIcons[$i % count($statIcons)] !!}</svg>
                                <div>
                                    <dt class="sr-only">{{ $stat['label'] }}</dt>
                                    <dd class="text-lg sm:text-xl font-extrabold leading-none">{{ $stat['value'] }}</dd>
                                    <p class="mt-1 text-xs text-white/75 leading-tight" aria-hidden="true">{{ $stat['label'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>
    </section>

    {{-- Pencarian kendaraan: kartu melayang di tepi bawah hero --}}
    <div class="container-page relative z-10 -mt-14">
        @include('partials.home-search', ['options' => $options])
    </div>

    {{-- Motto --}}
    @if ($mottos->isNotEmpty())
        <section class="container-page py-14" aria-label="Janji layanan">
            @php
                $cols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][min($mottos->count(), 3)];
            @endphp
            <ul class="grid gap-6 {{ $cols }} md:divide-x md:divide-line">
                @foreach ($mottos as $motto)
                    <li class="md:px-6 md:first:pl-0 md:last:pr-0">
                        <h2 class="text-2xl">{{ $motto->title }}</h2>
                        @if ($motto->description)
                            <p class="mt-2 text-road-soft">{{ $motto->description }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Unit unggulan --}}
    @if ($featured->isNotEmpty())
        <section class="container-page py-8" aria-labelledby="h-unggulan">
            <div class="flex items-end justify-between gap-4 mb-7">
                <div>
                    <h2 id="h-unggulan" class="text-3xl title-bar">Unit pilihan</h2>
                    <p class="mt-1 text-road-soft">Unit unggulan kami. Tanyakan ketersediaannya lewat WhatsApp.</p>
                </div>
                <a href="{{ route('vehicles.index') }}" class="shrink-0 font-bold text-brand hover:underline underline-offset-4">Lihat semua armada &rarr;</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($featured as $vehicle)
                    <x-vehicle-card :vehicle="$vehicle" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Kategori --}}
    @if ($categories->isNotEmpty())
        <section class="container-page py-12" aria-labelledby="h-kategori">
            <h2 id="h-kategori" class="text-3xl title-bar mb-7">Pilih jenis kendaraan</h2>
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    @php
                        $href = $category->slug === \App\Models\VehicleCategory::MOTOR_SLUG
                            ? route('motor.index')
                            : route('vehicles.index', ['category' => $category->slug]);
                    @endphp
                    <li>
                        <a href="{{ $href }}" class="block h-full card card-hover p-5">
                            <span class="block text-xl font-extrabold text-ink">{{ $category->name }}</span>
                            <span class="mt-1 inline-block rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-bold text-brand">{{ $category->published_vehicles_count }} unit</span>
                            @if ($category->description)
                                <span class="block mt-3 text-sm text-road-soft">{{ \Illuminate\Support\Str::limit($category->description, 90) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Paket wisata unggulan --}}
    @if ($tours->isNotEmpty())
        <section class="container-page py-12" aria-labelledby="h-wisata">
            <div class="flex items-end justify-between gap-4 mb-7">
                <h2 id="h-wisata" class="text-3xl title-bar">Paket wisata</h2>
                <a href="{{ route('tours.index') }}" class="shrink-0 font-bold text-brand hover:underline underline-offset-4">Semua paket &rarr;</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tours as $tour)
                    <x-tour-card :tour="$tour" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Keunggulan --}}
    @if ($advantages->isNotEmpty())
        <section class="bg-white border-y border-line" aria-labelledby="h-keunggulan">
            <div class="container-page py-14">
                <h2 id="h-keunggulan" class="text-3xl title-bar mb-8">Kenapa memilih kami</h2>
                <ul class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($advantages as $advantage)
                        <li class="flex gap-4">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand" aria-hidden="true">
                                <svg viewBox="0 0 20 20" class="size-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 10.5 3.5 3.5 7.5-8"/></svg>
                            </span>
                            <div>
                                <h3 class="text-lg">{{ $advantage->title }}</h3>
                                @if ($advantage->description)
                                    <p class="mt-1 text-road-soft">{{ $advantage->description }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Galeri --}}
    @if ($gallery->count() >= 4)
        <section class="container-page py-14" aria-labelledby="h-galeri">
            <h2 id="h-galeri" class="text-3xl title-bar mb-7">Galeri armada</h2>
            <ul class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($gallery->take(intdiv($gallery->count(), 4) * 4) as $photo)
                    <li class="group relative overflow-hidden rounded-2xl border border-line bg-white aspect-[4/3]">
                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt_text ?: $photo->vehicle?->name }}" loading="lazy"
                             class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Testimoni --}}
    @if ($testimonials->isNotEmpty())
        <section class="container-page py-12" aria-labelledby="h-testimoni">
            <h2 id="h-testimoni" class="text-3xl title-bar mb-7">Kata pelanggan</h2>
            <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <li class="card p-6 flex flex-col">
                        @if ($testimonial->rating)
                            <p class="text-amber-500" role="img" aria-label="Bintang {{ $testimonial->rating }} dari 5">
                                <span aria-hidden="true">{{ str_repeat('★', $testimonial->rating) }}<span class="text-line">{{ str_repeat('★', 5 - $testimonial->rating) }}</span></span>
                            </p>
                        @endif
                        <blockquote class="mt-2 text-road flex-1">&ldquo;{{ $testimonial->content }}&rdquo;</blockquote>
                        <p class="mt-4 text-sm font-bold text-ink">{{ $testimonial->customer_display_name }}
                            @if ($testimonial->source_label)
                                <span class="font-normal text-road-soft">&middot; {{ $testimonial->source_label }}</span>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="bg-white border-y border-line" aria-labelledby="h-faq">
            <div class="container-page container-text py-14">
                <h2 id="h-faq" class="text-3xl title-bar mb-7">Pertanyaan yang sering diajukan</h2>
                <div class="card divide-y divide-line">
                    @foreach ($faqs as $faq)
                        <details class="group p-5">
                            <summary class="cursor-pointer font-bold text-ink flex items-center justify-between gap-4 list-none">
                                <span>{{ $faq->question }}</span>
                                <span aria-hidden="true" class="text-accent text-xl leading-none group-open:rotate-45 transition-transform">+</span>
                            </summary>
                            <p class="mt-3 text-road-soft">{!! nl2br(e($faq->answer)) !!}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Lokasi --}}
    @if ($site['address'] || $mapsUrl)
        <section class="container-page py-14" aria-labelledby="h-lokasi">
            <h2 id="h-lokasi" class="text-3xl title-bar mb-7">Kunjungi lokasi kami</h2>
            <div class="grid gap-6 lg:grid-cols-5">
                <div class="card p-6 lg:col-span-2 self-start">
                    @if ($site['address'])
                        <p class="text-sm text-road-soft">Alamat</p>
                        <p class="mt-1 text-lg font-bold text-ink">{{ $site['address'] }}</p>
                    @endif
                    @if ($site['phone'])
                        <p class="mt-4 text-sm text-road-soft">Telepon</p>
                        <p class="mt-1 font-bold text-ink">{{ $site['phone'] }}</p>
                    @endif
                    <div class="mt-5 flex flex-wrap gap-2">
                        @if ($site['whatsapp'])
                            <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm">Chat WhatsApp</a>
                        @endif
                        <a href="{{ route('contact') }}" class="btn btn-outline btn-sm">Halaman kontak</a>
                    </div>
                </div>
                @if ($mapsUrl)
                    <div class="lg:col-span-3 overflow-hidden rounded-2xl border border-line bg-white aspect-[16/10]">
                        <iframe src="{{ $mapsUrl }}" title="Lokasi {{ $site['name'] }}" class="size-full" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Ajakan konsultasi --}}
    @if ($site['whatsapp'])
        <section class="container-page py-12">
            <div class="relative overflow-hidden rounded-3xl bg-brand text-white p-8 md:p-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="absolute -right-16 -top-10 h-[160%] w-40 -skew-x-[24deg] bg-accent/25 pointer-events-none" aria-hidden="true"></div>
                <div class="relative">
                    <h2 class="text-3xl text-white">Belum yakin pilih unit yang mana?</h2>
                    <p class="mt-2 text-white/80 max-w-prose">Ceritakan kebutuhan dan rencana tanggal sewa Anda. Kami bantu cek ketersediaan unit.</p>
                </div>
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="relative btn btn-light wa-icon shrink-0">Konsultasi lewat WhatsApp</a>
            </div>
        </section>
    @endif
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::business($site), \App\Support\StructuredData::website($site['name']), \App\Support\StructuredData::faq($faqs)])" />
@endpush
