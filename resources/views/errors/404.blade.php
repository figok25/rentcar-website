@extends('layouts.public')

@section('title', 'Halaman tidak ditemukan')
@section('robots', 'noindex')

@section('content')
    <div class="container-page container-text py-16 text-center">
        <p class="text-7xl font-extrabold tracking-tight text-accent" aria-hidden="true">404</p><p class="sr-only">Kode kesalahan 404</p>
        <h1 class="mt-2 text-4xl">Halaman tidak ditemukan</h1>
        <p class="mt-3 text-road-soft max-w-prose mx-auto">Alamat yang Anda buka tidak ada atau unitnya sudah tidak ditampilkan. Coba cari dari beranda atau daftar armada.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke beranda</a>
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline">Lihat armada</a>
        </div>
    </div>
@endsection
