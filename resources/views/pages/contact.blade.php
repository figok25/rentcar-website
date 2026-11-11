@extends('layouts.public')

@section('title', 'Kontak')
@section('description', 'Hubungi '.$site['name'].' untuk menanyakan ketersediaan unit dan harga sewa.')

@section('content')
    <div class="container-page py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <span>Kontak</span>
        </nav>
        <h1 class="text-4xl md:text-5xl">Kontak</h1>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            <div class="card divide-y divide-line self-start">
                @if ($site['whatsapp'])
                    <div class="p-5">
                        <p class="text-sm text-road-soft">WhatsApp</p>
                        <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa mt-2">Chat WhatsApp</a>
                    </div>
                @endif
                @if ($site['phone'])
                    <div class="p-5">
                        <p class="text-sm text-road-soft">Telepon</p>
                        <p class="mt-1 text-lg">{{ $site['phone'] }}</p>
                    </div>
                @endif
                @if ($site['address'])
                    <div class="p-5">
                        <p class="text-sm text-road-soft">Alamat</p>
                        <p class="mt-1">{!! nl2br(e($site['address'])) !!}</p>
                    </div>
                @endif
                @if ($site['instagram'] || $site['facebook'])
                    <div class="p-5 flex flex-wrap gap-4">
                        @if ($site['instagram'])
                            <a class="underline" href="{{ $site['instagram'] }}" target="_blank" rel="noopener">Instagram</a>
                        @endif
                        @if ($site['facebook'])
                            <a class="underline" href="{{ $site['facebook'] }}" target="_blank" rel="noopener">Facebook</a>
                        @endif
                    </div>
                @endif
                @unless ($site['whatsapp'] || $site['phone'] || $site['address'])
                    <p class="p-5 text-road-soft">Informasi kontak belum diisi.</p>
                @endunless
            </div>

            @if ($mapsUrl)
                <div class="rounded-2xl overflow-hidden border border-line bg-white aspect-[4/3]">
                    <iframe src="{{ $mapsUrl }}" title="Lokasi {{ $site['name'] }}" class="w-full h-full" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([
        \App\Support\StructuredData::business($site),
        \App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Kontak', route('contact')]]),
    ])" />
@endpush
