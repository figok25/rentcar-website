@extends('layouts.public')

@php
    $title = $kind->title();
    $selectedCategory = collect($options['categories'])->firstWhere('slug', $filters['category'] ?? null);
    if ($selectedCategory) {
        $title = $selectedCategory->name;
    }
@endphp

@section('title', $title.($vehicles->currentPage() > 1 ? ' - halaman '.$vehicles->currentPage() : ''))
@section('description', $selectedCategory?->description ? \Illuminate\Support\Str::limit($selectedCategory->description, 155) : $kind->description())

@section('content')
    <div class="bg-white border-b border-line">
        <div class="container-page py-8 md:py-10">
            <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-3">
                <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
                <span aria-hidden="true">/</span>
                <span>{{ $kind->title() }}</span>
            </nav>
            <h1 class="text-4xl md:text-5xl">{{ $title }}</h1>
            <p class="mt-3 text-road-soft max-w-prose">{{ $selectedCategory?->description ?: $kind->description() }}</p>
        </div>
    </div>

    <div class="container-page py-8">
        <div x-data="{ open: {{ $hasFilters ? 'true' : 'false' }} }">
            <button type="button" class="btn btn-outline lg:hidden w-full" @click="open = !open"
                    :aria-expanded="open.toString()" aria-controls="panel-filter">
                <span x-text="open ? 'Sembunyikan filter' : 'Tampilkan filter'">Tampilkan filter</span>
            </button>

            <div id="panel-filter" class="mt-4 lg:mt-0 card p-4 lg:block!" x-show="open" x-cloak>
                @include('partials.vehicle-filters', [
                    'action' => route($kind->indexRoute()),
                    'filters' => $filters,
                    'options' => $options,
                ])
            </div>
        </div>

        <p class="mt-6 text-sm text-road-soft" aria-live="polite">
            @if ($vehicles->total() > 0)
                Menampilkan {{ $vehicles->firstItem() }}-{{ $vehicles->lastItem() }} dari {{ $vehicles->total() }} kendaraan
            @endif
        </p>

        @if ($vehicles->isEmpty())
            <div class="mt-4 bg-white border border-dashed border-line rounded-2xl p-10 text-center">
                @if ($hasFilters)
                    <h2 class="text-2xl">Tidak ada kendaraan yang cocok</h2>
                    <p class="mt-2 text-road-soft">Coba longgarkan filter atau hapus kata kunci pencarian.</p>
                    <a href="{{ route($kind->indexRoute()) }}" class="btn btn-primary mt-5">Reset filter</a>
                @else
                    <h2 class="text-2xl">Belum ada kendaraan yang ditampilkan</h2>
                    <p class="mt-2 text-road-soft">Daftar unit sedang disiapkan. Hubungi kami untuk menanyakan unit yang tersedia.</p>
                    @if ($site['whatsapp'])
                        <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa mt-5">Chat WhatsApp</a>
                    @endif
                @endif
            </div>
        @else
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vehicles as $vehicle)
                    <x-vehicle-card :vehicle="$vehicle" heading="h2" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
@endsection

@if ($hasFilters)
    @section('robots', 'noindex,follow')
@endif

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], [$kind->title(), route($kind->indexRoute())]])])" />
@endpush
