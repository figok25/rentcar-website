@extends('layouts.public')

@php
    use App\Support\Money;

    $images = $vehicle->images;
    $primary = $images->firstWhere('is_primary', true) ?? $images->first();
    $waMessage = 'Halo, saya tertarik dengan '.$vehicle->name.'. Mohon info ketersediaan dan harganya.';
    $waUrl = \App\Services\WhatsAppLink::url($waMessage);
    $description = $vehicle->meta_description
        ?: \Illuminate\Support\Str::limit(strip_tags((string) $vehicle->description) ?: $vehicle->name.' - '.$vehicle->category->name.' dengan transmisi '.strtolower($vehicle->transmission->label()).'.', 155);

    // Ikon garis untuk baris spesifikasi (viewBox 24).
    $specs = [
        ['Transmisi', $vehicle->transmission->label(), '<circle cx="12" cy="12" r="3"/><path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1M7.7 16.3l-2.1 2.1"/>'],
        ['Bahan bakar', $vehicle->fuel_type->label(), '<path d="M12 3s6 6.2 6 10.5a6 6 0 0 1-12 0C6 9.2 12 3 12 3Z"/>'],
        ['Kapasitas', $vehicle->passenger_capacity.' penumpang', '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>'],
        ['Tahun', $vehicle->year, '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4m8-4v4M4 10h16"/>'],
        ['Model', $vehicle->model, '<path d="M4 12.5V5h7.5L20 13.5 13.5 20 4 12.5Z"/><circle cx="8.5" cy="9" r="1"/>'],
    ];
@endphp

@section('title', $vehicle->meta_title ?: $vehicle->name)
@section('description', $description)
@if ($primary)
    @section('image', $primary->url())
@endif

@section('content')
    <div class="container-page py-6 md:py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-5">
            <a href="{{ route('home') }}" class="hover:text-brand hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route($kind->indexRoute()) }}" class="hover:text-brand hover:underline">{{ $kind->title() }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-ink font-semibold">{{ $vehicle->name }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-5 lg:items-start">
            {{-- Galeri: menempel saat halaman di-scroll di layar lebar --}}
            <div class="lg:col-span-3 lg:sticky lg:top-24" x-data="{ current: 0, items: @js($images->map(fn ($i) => ['src' => $i->url(), 'alt' => $i->alt_text ?: $vehicle->name])->values()) }">
                @if ($images->isNotEmpty())
                    <div class="relative rounded-3xl overflow-hidden border border-line bg-white aspect-[4/3] shadow-card">
                        <img :src="items[current].src" :alt="items[current].alt" class="size-full object-contain p-4 sm:p-6"
                             src="{{ $primary->url() }}" alt="{{ $primary->alt_text ?: $vehicle->name }}" fetchpriority="high">
                    </div>
                    @if ($images->count() > 1)
                        <ul class="mt-3 grid grid-cols-4 sm:grid-cols-5 gap-2.5" aria-label="Foto lainnya">
                            @foreach ($images as $index => $image)
                                <li>
                                    <button type="button" @click="current = {{ $index }}"
                                            :aria-current="current === {{ $index }} ? 'true' : 'false'"
                                            :class="current === {{ $index }} ? 'border-brand ring-2 ring-brand/30' : 'border-line opacity-80 hover:opacity-100'"
                                            class="block w-full rounded-xl overflow-hidden aspect-[4/3] border-2 bg-white transition"
                                            aria-label="Tampilkan foto {{ $index + 1 }}">
                                        <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-contain p-1.5">
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <div class="rounded-3xl border border-dashed border-[#b9c8d6] bg-white aspect-[4/3] grid place-items-center text-road-soft">
                        Foto belum tersedia
                    </div>
                @endif
            </div>

            {{-- Info utama --}}
            <div class="lg:col-span-2 space-y-7">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$vehicle->status" />
                        @if ($vehicle->is_featured)
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand px-2.5 py-1 text-xs font-bold text-white">
                                <svg viewBox="0 0 20 20" class="size-3 text-accent-light" fill="currentColor" aria-hidden="true"><path d="m10 1.8 2.5 5.1 5.6.8-4.1 3.9 1 5.6L10 14.6l-5 2.6 1-5.6-4.1-3.9 5.6-.8Z"/></svg>
                                Populer
                            </span>
                        @endif
                    </div>
                    <h1 class="mt-3 text-3xl sm:text-4xl leading-tight">{{ $vehicle->name }}</h1>
                    <p class="mt-1.5 font-semibold text-brand">{{ collect([$vehicle->brand, $vehicle->category->name])->filter()->implode(', ') }}</p>

                    <p class="mt-4 flex gap-2.5 rounded-xl bg-brand-soft p-3.5 text-sm text-road">
                        <svg viewBox="0 0 24 24" class="size-5 shrink-0 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8.5v.01"/></svg>
                        <span>Status unit diperbarui admin. Ketersediaan pada tanggal tertentu dikonfirmasi lewat WhatsApp.</span>
                    </p>

                    @if ($waUrl)
                        <div class="mt-4 flex flex-wrap gap-2.5">
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-wa">Tanya via WhatsApp</a>
                            <a href="#form-sewa" class="btn btn-outline">Isi rencana sewa</a>
                        </div>
                    @endif
                </div>

                <section aria-labelledby="h-spek">
                    <h2 id="h-spek" class="text-2xl title-bar">Spesifikasi</h2>
                    <dl class="mt-3 card divide-y divide-line">
                        @foreach ($specs as [$label, $value, $icon])
                            @if (filled($value))
                                <div class="flex items-center justify-between gap-4 px-4 py-3">
                                    <dt class="flex items-center gap-2.5 text-road-soft">
                                        <svg viewBox="0 0 24 24" class="size-[1.125rem] text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                                        {{ $label }}
                                    </dt>
                                    <dd class="font-bold text-ink text-right">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </section>

                <section aria-labelledby="h-harga">
                    <h2 id="h-harga" class="text-2xl title-bar">Harga sewa</h2>
                    @if ($vehicle->activeRates->isEmpty())
                        <p class="mt-3 text-road-soft">Harga belum dicantumkan. Tanyakan langsung lewat WhatsApp.</p>
                    @else
                        <ul class="mt-3 space-y-3">
                            @foreach ($vehicle->activeRates->sortBy(fn ($r) => $r->service_type->value) as $rate)
                                <li class="card p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg">{{ $rate->service_type->label() }}</h3>
                                        <p class="leading-none text-right">
                                            <span class="text-2xl font-extrabold text-accent">{{ Money::rupiah($rate->price_per_day) }}</span>
                                            <span class="text-sm text-road-soft">/hari</span>
                                        </p>
                                    </div>
                                    <ul class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                        @foreach ([['Driver', $rate->includes_driver], ['BBM', $rate->includes_fuel]] as [$what, $included])
                                            <li @class([
                                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1',
                                                'bg-green-100 text-sign-green-dark' => $included,
                                                'bg-paper text-road-soft' => ! $included,
                                            ])>
                                                @if ($included)
                                                    <svg viewBox="0 0 20 20" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 10.5 3.5 3.5 7.5-8"/></svg>
                                                @else
                                                    <svg viewBox="0 0 20 20" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M5 10h10"/></svg>
                                                @endif
                                                {{ $what }}: {{ $included ? 'termasuk' : 'tidak termasuk' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($rate->notes)
                                        <p class="mt-3 text-sm text-road">{{ $rate->notes }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                @if ($waUrl)
                    @include('partials.rental-inquiry-form', ['vehicle' => $vehicle])
                @endif
            </div>
        </div>

        @if ($vehicle->description)
            <section class="mt-12" aria-labelledby="h-deskripsi">
                <h2 id="h-deskripsi" class="text-2xl title-bar">Tentang unit ini</h2>
                <div class="mt-3 text-road max-w-prose leading-relaxed">{!! nl2br(e($vehicle->description)) !!}</div>
            </section>
        @endif

        {{-- Biaya tambahan --}}
        @if ($fees->isNotEmpty())
            <section class="mt-14" aria-labelledby="h-biaya">
                <h2 id="h-biaya" class="text-2xl title-bar">Biaya tambahan</h2>
                <div class="mt-4 overflow-x-auto card">
                    <table class="w-full text-left">
                        <thead class="bg-brand-soft text-sm text-brand">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-bold">Biaya</th>
                                <th scope="col" class="px-4 py-3 font-bold">Nominal</th>
                                <th scope="col" class="px-4 py-3 font-bold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($fees as $fee)
                                <tr>
                                    <th scope="row" class="px-4 py-3 font-bold text-ink">{{ $fee->name }}</th>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-accent">{{ $fee->amount > 0 ? Money::rupiah($fee->amount) : 'Sesuai konfirmasi' }}</td>
                                    <td class="px-4 py-3 text-road-soft">{{ $fee->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Syarat sewa (diisi admin) --}}
        @if ($requirementGroups->isNotEmpty())
            <section class="mt-14" aria-labelledby="h-syarat">
                <h2 id="h-syarat" class="text-2xl title-bar">Syarat dan ketentuan sewa</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($requirementGroups as $group)
                        <div class="card p-5">
                            <h3 class="text-lg">{{ $group->name }}</h3>
                            @if ($group->description)
                                <p class="text-sm text-road-soft mt-1">{{ $group->description }}</p>
                            @endif
                            <ul class="mt-3 space-y-2">
                                @foreach ($group->requirements as $requirement)
                                    <li class="flex gap-2.5">
                                        <span aria-hidden="true" class="mt-2 size-2 shrink-0 rounded-full bg-accent"></span>
                                        <span>{{ $requirement->requirement_text }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kendaraan terkait --}}
        @if ($related->isNotEmpty())
            <section class="mt-14" aria-labelledby="h-terkait">
                <h2 id="h-terkait" class="text-2xl title-bar mb-5">Kendaraan serupa</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-vehicle-card :vehicle="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([
        \App\Support\StructuredData::vehicle($vehicle),
        \App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], [$kind->title(), route($kind->indexRoute())], [$vehicle->name, $vehicle->publicUrl()]]),
    ])" />
@endpush
