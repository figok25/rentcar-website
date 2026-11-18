@extends('layouts.public')

@section('title', 'Syarat Sewa')
@section('description', 'Syarat dan ketentuan sewa kendaraan. Ketersediaan dan harga final dikonfirmasi admin lewat WhatsApp.')

@section('content')
    <div class="container-page container-narrow py-8">
        <nav aria-label="Jejak halaman" class="text-sm text-road-soft mb-5">
            <a href="{{ route('home') }}" class="hover:text-brand hover:underline">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-ink font-semibold">Syarat Sewa</span>
        </nav>
        <h1 class="text-4xl md:text-5xl title-bar">Syarat Sewa</h1>
        <p class="mt-3 text-road-soft max-w-prose">Siapkan persyaratan berikut sebelum menyewa. Kami konfirmasi ketersediaan unit dan harga final lewat WhatsApp.</p>

        @forelse ($groups as $group)
            <section class="mt-8" aria-labelledby="g-{{ $group->id }}">
                <h2 id="g-{{ $group->id }}" class="text-2xl title-bar">{{ $group->name }}</h2>
                @if ($group->category)
                    <p class="text-sm text-road-soft">Untuk kategori {{ $group->category->name }}</p>
                @endif
                @if ($group->description)
                    <p class="mt-1 text-road-soft">{{ $group->description }}</p>
                @endif
                <ul class="mt-3 card divide-y divide-line">
                    @foreach ($group->requirements as $requirement)
                        <li class="p-4 flex gap-3">
                            <span aria-hidden="true" class="mt-2 size-2 rounded-full bg-accent shrink-0"></span>
                            <span>{{ $requirement->requirement_text }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="mt-8 card p-6 text-road-soft">Syarat sewa belum dipublikasikan. Silakan tanyakan langsung kepada kami.</p>
        @endforelse

        @if ($site['whatsapp'])
            <a href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-wa mt-8">Tanya lewat WhatsApp</a>
        @endif
    </div>
@endsection

@push('jsonld')
    <x-json-ld :data="\App\Support\StructuredData::graph([\App\Support\StructuredData::breadcrumbs([['Beranda', route('home')], ['Syarat Sewa', route('requirements')]])])" />
@endpush
