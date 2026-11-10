@props(['site'])
<footer class="bg-plate-ink text-white mt-16">
    <div class="mx-auto max-w-6xl px-4 py-12 grid gap-10 md:grid-cols-3">
        <div>
            <x-plate small class="px-3 py-1 text-lg leading-none inline-block">{{ $site['name'] }}</x-plate>
            @if ($site['footerText'])
                <p class="mt-4 text-sm text-white/75 max-w-xs">{{ $site['footerText'] }}</p>
            @endif
        </div>

        <nav aria-label="Menu footer">
            <h2 class="font-display text-lg">Jelajahi</h2>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                <li><a class="hover:text-white" href="{{ route('home') }}">Beranda</a></li>
                <li><a class="hover:text-white" href="{{ route('vehicles.index') }}">Rental Mobil</a></li>
                @if ($site['showMotor'])
                    <li><a class="hover:text-white" href="{{ route('motor.index') }}">Rental Motor</a></li>
                @endif
                <li><a class="hover:text-white" href="{{ route('requirements') }}">Syarat Sewa</a></li>
                <li><a class="hover:text-white" href="{{ route('contact') }}">Kontak</a></li>
            </ul>
        </nav>

        <div>
            <h2 class="font-display text-lg">Hubungi kami</h2>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                @if ($site['whatsapp'])
                    <li><a class="hover:text-white underline" href="{{ $site['whatsapp'] }}" target="_blank" rel="noopener">WhatsApp</a></li>
                @endif
                @if ($site['phone'])
                    <li>Telepon: {{ $site['phone'] }}</li>
                @endif
                @if ($site['address'])
                    <li>{{ $site['address'] }}</li>
                @endif
                @if ($site['instagram'])
                    <li><a class="hover:text-white underline" href="{{ $site['instagram'] }}" target="_blank" rel="noopener">Instagram</a></li>
                @endif
                @if ($site['facebook'])
                    <li><a class="hover:text-white underline" href="{{ $site['facebook'] }}" target="_blank" rel="noopener">Facebook</a></li>
                @endif
            </ul>
        </div>
    </div>
    <div class="border-t border-white/15">
        <p class="mx-auto max-w-6xl px-4 py-4 text-xs text-white/60">&copy; {{ date('Y') }} {{ $site['name'] }}. Ketersediaan unit dikonfirmasi admin melalui WhatsApp.</p>
    </div>
</footer>
