{{--
    Paginasi bermerek. Dipakai lewat $paginator->links('vendor.pagination.brand').
    Tautan rel="prev" dan rel="next" dipertahankan untuk mesin pencari.
--}}
@if ($paginator->hasPages())
    @php
        $item = 'inline-flex h-11 min-w-11 items-center justify-center rounded-xl border px-3 text-sm font-bold transition-colors';
    @endphp
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span class="{{ $item }} border-line bg-white text-road-soft/60 cursor-not-allowed" aria-disabled="true">&larr; Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $item }} border-line bg-white text-ink hover:border-brand hover:text-brand">&larr; Sebelumnya</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-11 items-center px-1 text-road-soft" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $item }} border-brand bg-brand text-white" aria-current="page"><span class="sr-only">Halaman </span>{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $item }} border-line bg-white text-ink hover:border-brand hover:text-brand" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $item }} border-line bg-white text-ink hover:border-brand hover:text-brand">Berikutnya &rarr;</a>
        @else
            <span class="{{ $item }} border-line bg-white text-road-soft/60 cursor-not-allowed" aria-disabled="true">Berikutnya &rarr;</span>
        @endif
    </nav>
@endif
