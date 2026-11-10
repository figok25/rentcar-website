@extends('layouts.public')

@section('title', 'Syarat Sewa')
@section('description', 'Syarat dan ketentuan sewa kendaraan. Ketersediaan dan harga final dikonfirmasi admin lewat WhatsApp.')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-4">
            <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <span>Syarat Sewa</span>
        </nav>
        <h1 class="text-4xl md:text-5xl">Syarat Sewa</h1>
        <p class="mt-3 text-road-soft max-w-prose">Siapkan persyaratan berikut sebelum menyewa. Kami konfirmasi ketersediaan unit dan harga final lewat WhatsApp.</p>

        @forelse ($groups as $group)
            <section class="mt-8" aria-labelledby="g-{{ $group->id }}">
                <h2 id="g-{{ $group->id }}" class="text-2xl">{{ $group->name }}</h2>
                @if ($group->category)
                    <p class="text-sm text-road-soft">Untuk kategori {{ $group->category->name }}</p>
                @endif
                @if ($group->description)
                    <p class="mt-1 text-road-soft">{{ $group->description }}</p>
                @endif
                <ul class="mt-3 bg-white border border-line rounded-xl divide-y divide-line">
                    @foreach ($group->requirements as $requirement)
                        <li class="p-4 flex gap-3">
                            <span aria-hidden="true" class="mt-2 size-2 rounded-full bg-plate shrink-0 ring-1 ring-plate-ink"></span>
                            <span>{{ $requirement->requirement_text }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="mt-8 bg-white border border-line rounded-xl p-6 text-road-soft">Syarat sewa belum dipublikasikan. Silakan tanyakan langsung kepada kami.</p>
        @endforelse

        @if ($site['whatsapp'])
            <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa mt-8">Tanya lewat WhatsApp</a>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Syarat Sewa', route('requirements')]])])" />
@endpush
