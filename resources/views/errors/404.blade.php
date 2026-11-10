@extends('layouts.public')

@section('title', 'Halaman tidak ditemukan')
@section('robots', 'noindex')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-16 text-center">
        <x-plate class="inline-block px-6 py-2 text-5xl">404</x-plate>
        <h1 class="mt-6 text-4xl">Halaman tidak ditemukan</h1>
        <p class="mt-3 text-road-soft max-w-prose mx-auto">Alamat yang Anda buka tidak ada atau unitnya sudah tidak ditampilkan. Coba cari dari beranda atau daftar armada.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke beranda</a>
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline">Lihat armada</a>
        </div>
    </div>
@endsection
