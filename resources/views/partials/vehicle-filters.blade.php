{{-- Form filter katalog. Variabel: $action, $filters, $options, $showSort --}}
@php
    $showSort = $showSort ?? true;
    $f = fn ($key) => $filters[$key] ?? '';
@endphp
<form method="GET" action="{{ $action }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" role="search" aria-label="Cari kendaraan">
    <div class="sm:col-span-2">
        <label for="f-q" class="block mb-1.5">Cari kendaraan</label>
        <input id="f-q" type="search" name="q" value="{{ $f('q') }}" maxlength="60" placeholder="Nama, merek, atau model" class="field">
    </div>

    <div>
        <label for="f-category" class="block mb-1.5">Kategori</label>
        <select id="f-category" name="category" class="field">
            <option value="">Semua kategori</option>
            @foreach ($options['categories'] as $category)
                <option value="{{ $category->slug }}" @selected($f('category') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-brand" class="block mb-1.5">Merek</label>
        <select id="f-brand" name="brand" class="field">
            <option value="">Semua merek</option>
            @foreach ($options['brands'] as $brand)
                <option value="{{ $brand }}" @selected($f('brand') === $brand)>{{ $brand }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-trans" class="block mb-1.5">Transmisi</label>
        <select id="f-trans" name="transmission" class="field">
            <option value="">Semua</option>
            @foreach (\App\Enums\TransmissionType::options() as $value => $label)
                <option value="{{ $value }}" @selected($f('transmission') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-cap" class="block mb-1.5">Kapasitas</label>
        <select id="f-cap" name="min_capacity" class="field">
            <option value="">Berapa saja</option>
            @foreach (\App\Services\VehicleCatalog::CAPACITY_OPTIONS as $cap)
                <option value="{{ $cap }}" @selected((string) $f('min_capacity') === (string) $cap)>{{ $cap }} penumpang atau lebih</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-min" class="block mb-1.5">Harga per hari, minimum (Rp)</label>
        <input id="f-min" type="number" name="min_price" value="{{ $f('min_price') }}" min="0" step="10000" inputmode="numeric" class="field">
    </div>

    <div>
        <label for="f-max" class="block mb-1.5">Harga per hari, maksimum (Rp)</label>
        <input id="f-max" type="number" name="max_price" value="{{ $f('max_price') }}" min="0" step="10000" inputmode="numeric" class="field">
    </div>

    @if ($showSort)
        <div>
            <label for="f-sort" class="block mb-1.5">Urutkan</label>
            <select id="f-sort" name="sort" class="field">
                <option value="featured" @selected(($f('sort') ?: 'featured') === 'featured')>Unggulan</option>
                <option value="price_asc" @selected($f('sort') === 'price_asc')>Harga termurah</option>
                <option value="price_desc" @selected($f('sort') === 'price_desc')>Harga termahal</option>
            </select>
        </div>
    @endif

    <div @class(['sm:col-span-2 flex flex-wrap items-end gap-2', 'lg:col-span-3 lg:justify-end' => $showSort, 'lg:col-span-4' => ! $showSort])>
        @if (! empty($filters))
            <a href="{{ $action }}" class="btn btn-outline">Reset filter</a>
        @endif
        <button type="submit" class="btn btn-accent px-7">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            Cari kendaraan
        </button>
    </div>
</form>
