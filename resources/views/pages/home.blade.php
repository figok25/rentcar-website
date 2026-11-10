@extends('layouts.public')

@section('title', 'Sewa Kendaraan')
@section('description', \Illuminate\Support\Str::limit($heroSubtitle ?: 'Rental kendaraan dengan unit terawat dan harga jelas. Cek unit, lalu konsultasi lewat WhatsApp.', 155))
@if ($heroImage)
    @section('image', $heroImage)
@endif

@section('content')
    {{-- Hero --}}
    <section class="bg-white border-b border-line">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-12 md:pt-16 md:pb-16 grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <x-plate as="h1" class="inline-block px-5 py-4 text-3xl sm:text-4xl md:text-5xl leading-tight">
                    {{ $heroTitle ?: 'Sewa Kendaraan Mudah dan Terpercaya' }}
                </x-plate>
                @if ($heroSubtitle)
                    <p class="mt-5 text-lg text-road-soft max-w-prose">{{ $heroSubtitle }}</p>
                @endif
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary">Lihat armada</a>
                    @if ($site['whatsapp'])
                        <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa">Chat WhatsApp</a>
                    @endif
                </div>
            </div>

            @if ($heroImage)
                <div class="rounded-xl overflow-hidden border border-line bg-paper aspect-[4/3]">
                    <img src="{{ $heroImage }}" alt="Unit kendaraan {{ $site['name'] }}" class="size-full object-cover">
                </div>
            @endif
        </div>

        {{-- Jalur kuning: pencarian kendaraan --}}
        <div class="bg-plate border-t-4 border-plate-ink">
            <div class="mx-auto max-w-6xl px-4 py-6">
                @include('partials.vehicle-filters', [
                    'action' => route('vehicles.index'),
                    'filters' => $filters,
                    'options' => $options,
                    'showSort' => false,
                ])
            </div>
        </div>
    </section>

    {{-- Motto --}}
    @if ($mottos->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12" aria-label="Janji layanan">
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
        <section class="mx-auto max-w-6xl px-4 py-8" aria-labelledby="h-unggulan">
            <div class="flex items-end justify-between gap-4 mb-6">
                <h2 id="h-unggulan" class="text-3xl">Unit pilihan</h2>
                <a href="{{ route('vehicles.index') }}" class="font-semibold underline underline-offset-4">Lihat semua armada</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $vehicle)
                    <x-vehicle-card :vehicle="$vehicle" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Kategori --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12" aria-labelledby="h-kategori">
            <h2 id="h-kategori" class="text-3xl mb-6">Pilih jenis kendaraan</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    @php
                        $href = $category->slug === \App\Models\VehicleCategory::MOTOR_SLUG
                            ? route('motor.index')
                            : route('vehicles.index', ['category' => $category->slug]);
                    @endphp
                    <li>
                        <a href="{{ $href }}" class="block h-full bg-white border border-line rounded-xl p-5 hover:border-plate-ink">
                            <span class="block font-display text-xl">{{ $category->name }}</span>
                            <span class="block mt-1 text-sm text-road-soft">{{ $category->published_vehicles_count }} unit</span>
                            @if ($category->description)
                                <span class="block mt-2 text-sm text-road-soft">{{ \Illuminate\Support\Str::limit($category->description, 90) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Keunggulan --}}
    @if ($advantages->isNotEmpty())
        <section class="bg-white border-y border-line" aria-labelledby="h-keunggulan">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <h2 id="h-keunggulan" class="text-3xl mb-6">Kenapa memilih kami</h2>
                <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($advantages as $advantage)
                        <li class="border-l-4 border-plate pl-4">
                            <h3 class="text-xl">{{ $advantage->title }}</h3>
                            @if ($advantage->description)
                                <p class="mt-1 text-road-soft">{{ $advantage->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Testimoni --}}
    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12" aria-labelledby="h-testimoni">
            <h2 id="h-testimoni" class="text-3xl mb-6">Kata pelanggan</h2>
            <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <li class="bg-white border border-line rounded-xl p-5 flex flex-col">
                        @if ($testimonial->rating)
                            <p class="text-plate-ink" role="img" aria-label="Bintang {{ $testimonial->rating }} dari 5">
                                <span aria-hidden="true">{{ str_repeat('★', $testimonial->rating) }}<span class="text-line">{{ str_repeat('★', 5 - $testimonial->rating) }}</span></span>
                            </p>
                        @endif
                        <blockquote class="mt-2 text-road flex-1">&ldquo;{{ $testimonial->content }}&rdquo;</blockquote>
                        <p class="mt-4 text-sm font-semibold">{{ $testimonial->customer_display_name }}
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
            <div class="mx-auto max-w-3xl px-4 py-12">
                <h2 id="h-faq" class="text-3xl mb-6">Pertanyaan yang sering diajukan</h2>
                <div class="border border-line rounded-xl divide-y divide-line">
                    @foreach ($faqs as $faq)
                        <details class="group p-4">
                            <summary class="cursor-pointer font-semibold flex items-center justify-between gap-4 list-none">
                                <span>{{ $faq->question }}</span>
                                <span aria-hidden="true" class="text-road-soft group-open:rotate-45 transition-transform">+</span>
                            </summary>
                            <p class="mt-2 text-road-soft">{!! nl2br(e($faq->answer)) !!}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Ajakan konsultasi --}}
    @if ($site['whatsapp'])
        <section class="mx-auto max-w-6xl px-4 py-12">
            <div class="bg-plate-ink text-white rounded-xl p-8 md:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-3xl">Belum yakin pilih unit yang mana?</h2>
                    <p class="mt-2 text-white/75 max-w-prose">Ceritakan kebutuhan dan rencana tanggal sewa Anda. Kami bantu cek ketersediaan unit.</p>
                </div>
                <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa shrink-0">Konsultasi lewat WhatsApp</a>
            </div>
        </section>
    @endif
@endsection
