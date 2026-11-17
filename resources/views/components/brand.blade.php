{{--
    Penanda merek untuk header dan footer.
    - Jika admin mengunggah logo di Pengaturan website, logo itu yang dipakai.
    - Jika belum, dipakai ikon bawaan (public/images/logo-mark.png) dan nama bisnis dua warna:
      kata pertama navy, sisanya merah (mis. "Empatra" / "DigiTech").
    Variabel: $site, $tone ('light' untuk latar putih, 'dark' untuk latar navy).
--}}
@props(['site', 'tone' => 'light'])
@php
    $dark = $tone === 'dark';
    $parts = preg_split('/\s+/', trim((string) $site['name']), 2) ?: [];
    $first = $parts[0] ?? (string) $site['name'];
    $rest = $parts[1] ?? null;
@endphp
<span {{ $attributes->class(['flex items-center gap-2.5']) }}>
    @if ($site['logo'])
        <span @class(['grid place-items-center', 'rounded-xl bg-white p-1.5' => $dark])>
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($site['logo']) }}" alt="{{ $site['name'] }}" class="h-9 w-auto max-w-44 object-contain">
        </span>
    @else
        <span @class(['grid place-items-center shrink-0', 'rounded-xl bg-white p-1.5' => $dark])>
            <img src="{{ asset('images/logo-mark.png') }}" alt="" width="43" height="40" class="{{ $dark ? 'h-8' : 'h-10' }} w-auto">
        </span>
        <span class="flex flex-col leading-[1.08] text-[1.0625rem] font-extrabold tracking-tight">
            <span class="{{ $dark ? 'text-white' : 'text-brand' }}">{{ $first }}</span>
            @if ($rest)
                <span class="{{ $dark ? 'text-accent-light' : 'text-accent' }}">{{ $rest }}</span>
            @endif
        </span>
    @endif
</span>
