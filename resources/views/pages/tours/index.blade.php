@extends('layouts.public')

@section('title', 'Paket Wisata')
@section('description', 'Pilihan paket wisata lengkap dengan rencana perjalanan. Tanyakan jadwal dan harga lewat WhatsApp.')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <span>Paket Wisata</span>
        </nav>
        <h1 class="text-4xl md:text-5xl">Paket Wisata</h1>
        <p class="mt-3 text-road-soft max-w-prose">Pilih paket yang cocok, lalu tanyakan jadwal, jumlah peserta, dan harga final lewat WhatsApp.</p>

        @if ($tours->isEmpty())
            <p class="mt-8 bg-white border border-line rounded-xl p-6 text-road-soft">Paket wisata belum tersedia.</p>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tours as $tour)
                    <x-tour-card :tour="$tour" heading="h2" />
                @endforeach
            </div>
            <div class="mt-8">{{ $tours->links() }}</div>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Paket Wisata', route('tours.index')]])])" />
@endpush
