@extends('layouts.public')

@section('title', 'Layanan Travel')
@section('description', 'Layanan travel antar kota dengan armada terawat. Tanyakan jadwal dan harga lewat WhatsApp.')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <span>Travel</span>
        </nav>
        <h1 class="text-4xl md:text-5xl">Layanan Travel</h1>
        <p class="mt-3 text-road-soft max-w-prose">Tanyakan jadwal keberangkatan, ketersediaan kursi, dan harga lewat WhatsApp.</p>

        @if ($services->isEmpty())
            <p class="mt-8 bg-white border border-line rounded-xl p-6 text-road-soft">Layanan travel belum tersedia.</p>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-travel-card :service="$service" heading="h2" />
                @endforeach
            </div>
            <div class="mt-8">{{ $services->links() }}</div>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Travel', route('travel.index')]])])" />
@endpush
