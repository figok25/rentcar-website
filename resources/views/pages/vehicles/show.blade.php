@extends('layouts.public')

@php
    use App\Support\Money;

    $images = $vehicle->images;
    $primary = $images->firstWhere('is_primary', true) ?? $images->first();
    $waMessage = 'Halo, saya tertarik dengan '.$vehicle->name.'. Mohon info ketersediaan dan harganya.';
    $waUrl = \App\Services\WhatsAppLink::url($waMessage);
    $description = $vehicle->meta_description
        ?: \Illuminate\Support\Str::limit(strip_tags((string) $vehicle->description) ?: $vehicle->name.' - '.$vehicle->category->name.' dengan transmisi '.strtolower($vehicle->transmission->label()).'.', 155);
@endphp

@section('title', $vehicle->meta_title ?: $vehicle->name)
@section('description', $description)
@if ($primary)
    @section('image', $primary->url())
@endif

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-6">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route($kind->indexRoute()) }}" class="hover:underline">{{ $kind->title() }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ $vehicle->name }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-5">
            {{-- Galeri --}}
            <div class="lg:col-span-3" x-data="{ current: 0, items: @js($images->map(fn ($i) => ['src' => $i->url(), 'alt' => $i->alt_text ?: $vehicle->name])->values()) }">
                @if ($images->isNotEmpty())
                    <div class="rounded-xl overflow-hidden border border-line bg-white aspect-[4/3]">
                        <img :src="items[current].src" :alt="items[current].alt" class="size-full object-cover"
                             src="{{ $primary->url() }}" alt="{{ $primary->alt_text ?: $vehicle->name }}">
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
                    <div class="rounded-xl border border-dashed border-line bg-white aspect-[4/3] grid place-items-center text-road-soft">
                        Foto belum tersedia
                    </div>
                @endif
            </div>

            {{-- Info utama --}}
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <x-status-badge :status="$vehicle->status" />
                    <h1 class="mt-3 text-4xl leading-tight">{{ $vehicle->name }}</h1>
                    <p class="mt-1 text-road-soft">{{ collect([$vehicle->brand, $vehicle->category->name])->filter()->implode(', ') }}</p>
                    <p class="mt-2 text-sm text-road-soft">Status unit diperbarui admin. Ketersediaan pada tanggal tertentu dikonfirmasi lewat WhatsApp.</p>
                </div>

                <section aria-labelledby="h-spek">
                    <h2 id="h-spek" class="text-2xl">Spesifikasi</h2>
                    <dl class="mt-2 bg-white border border-line rounded-xl divide-y divide-line">
                        @foreach ([
                            'Transmisi' => $vehicle->transmission->label(),
                            'Bahan bakar' => $vehicle->fuel_type->label(),
                            'Kapasitas' => $vehicle->passenger_capacity.' penumpang',
                            'Tahun' => $vehicle->year,
                            'Model' => $vehicle->model,
                        ] as $label => $value)
                            @if (filled($value))
                                <div class="flex justify-between gap-4 px-4 py-2.5">
                                    <dt class="text-road-soft">{{ $label }}</dt>
                                    <dd class="font-semibold text-right">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </section>

                <section aria-labelledby="h-harga">
                    <h2 id="h-harga" class="text-2xl">Harga sewa</h2>
                    @if ($vehicle->activeRates->isEmpty())
                        <p class="mt-2 text-road-soft">Harga belum dicantumkan. Tanyakan langsung lewat WhatsApp.</p>
                    @else
                        <ul class="mt-2 space-y-3">
                            @foreach ($vehicle->activeRates->sortBy(fn ($r) => $r->service_type->value) as $rate)
                                <li class="bg-white border border-line rounded-xl p-4">
                                    <div class="flex items-center justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg">{{ $rate->service_type->label() }}</h3>
                                        <x-plate small class="inline-flex items-baseline gap-1 px-3 py-1">
                                            <span class="text-lg">{{ Money::rupiah($rate->price_per_day) }}</span>
                                            <span class="text-sm font-semibold">/hari</span>
                                        </x-plate>
                                    </div>
                                    <ul class="mt-2 text-sm text-road-soft">
                                        <li>Driver: {{ $rate->includes_driver ? 'termasuk' : 'tidak termasuk' }}</li>
                                        <li>BBM: {{ $rate->includes_fuel ? 'termasuk' : 'tidak termasuk' }}</li>
                                    </ul>
                                    @if ($rate->notes)
                                        <p class="mt-2 text-sm">{{ $rate->notes }}</p>
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
            <section class="mt-10" aria-labelledby="h-deskripsi">
                <h2 id="h-deskripsi" class="text-2xl">Tentang unit ini</h2>
                <div class="mt-2 text-road max-w-prose leading-relaxed">{!! nl2br(e($vehicle->description)) !!}</div>
            </section>
        @endif

        {{-- Biaya tambahan --}}
        @if ($fees->isNotEmpty())
            <section class="mt-12" aria-labelledby="h-biaya">
                <h2 id="h-biaya" class="text-2xl">Biaya tambahan</h2>
                <div class="mt-3 overflow-x-auto bg-white border border-line rounded-xl">
                    <table class="w-full text-left">
                        <thead class="border-b border-line text-sm text-road-soft">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-semibold">Biaya</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Nominal</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($fees as $fee)
                                <tr>
                                    <th scope="row" class="px-4 py-3 font-semibold">{{ $fee->name }}</th>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $fee->amount > 0 ? Money::rupiah($fee->amount) : 'Sesuai konfirmasi' }}</td>
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
            <section class="mt-12" aria-labelledby="h-syarat">
                <h2 id="h-syarat" class="text-2xl">Syarat dan ketentuan sewa</h2>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    @foreach ($requirementGroups as $group)
                        <div class="bg-white border border-line rounded-xl p-5">
                            <h3 class="text-lg">{{ $group->name }}</h3>
                            @if ($group->description)
                                <p class="text-sm text-road-soft mt-1">{{ $group->description }}</p>
                            @endif
                            <ul class="mt-3 list-disc pl-5 space-y-1">
                                @foreach ($group->requirements as $requirement)
                                    <li>{{ $requirement->requirement_text }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kendaraan terkait --}}
        @if ($related->isNotEmpty())
            <section class="mt-12" aria-labelledby="h-terkait">
                <h2 id="h-terkait" class="text-2xl mb-4">Kendaraan serupa</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-vehicle-card :vehicle="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
