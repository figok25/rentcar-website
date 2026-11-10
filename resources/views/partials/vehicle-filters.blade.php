{{-- Form filter katalog. Variabel: $action, $filters, $options, $showSort --}}
@php
    $showSort = $showSort ?? true;
    $f = fn ($key) => $filters[$key] ?? '';
@endphp
<form method="GET" action="{{ $action }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" role="search" aria-label="Cari kendaraan">
    <div class="sm:col-span-2">
        <label for="f-q" class="block text-sm font-semibold mb-1">Cari kendaraan</label>
        <input id="f-q" type="search" name="q" value="{{ $f('q') }}" maxlength="60" placeholder="Nama, merek, atau model" class="field">
    </div>

    <div>
        <label for="f-category" class="block text-sm font-semibold mb-1">Kategori</label>
        <select id="f-category" name="category" class="field">
            <option value="">Semua kategori</option>
            @foreach ($options['categories'] as $category)
                <option value="{{ $category->slug }}" @selected($f('category') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-brand" class="block text-sm font-semibold mb-1">Merek</label>
        <select id="f-brand" name="brand" class="field">
            <option value="">Semua merek</option>
            @foreach ($options['brands'] as $brand)
                <option value="{{ $brand }}" @selected($f('brand') === $brand)>{{ $brand }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-trans" class="block text-sm font-semibold mb-1">Transmisi</label>
        <select id="f-trans" name="transmission" class="field">
            <option value="">Semua</option>
            @foreach (\App\Enums\TransmissionType::options() as $value => $label)
                <option value="{{ $value }}" @selected($f('transmission') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-cap" class="block text-sm font-semibold mb-1">Kapasitas</label>
        <select id="f-cap" name="min_capacity" class="field">
            <option value="">Berapa saja</option>
            @foreach (\App\Services\VehicleCatalog::CAPACITY_OPTIONS as $cap)
                <option value="{{ $cap }}" @selected((string) $f('min_capacity') === (string) $cap)>{{ $cap }} penumpang atau lebih</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="f-min" class="block text-sm font-semibold mb-1">Harga per hari, minimum (Rp)</label>
        <input id="f-min" type="number" name="min_price" value="{{ $f('min_price') }}" min="0" step="10000" inputmode="numeric" class="field">
    </div>

    <div>
        <label for="f-max" class="block text-sm font-semibold mb-1">Harga per hari, maksimum (Rp)</label>
        <input id="f-max" type="number" name="max_price" value="{{ $f('max_price') }}" min="0" step="10000" inputmode="numeric" class="field">
    </div>

    @if ($showSort)
        <div>
            <label for="f-sort" class="block text-sm font-semibold mb-1">Urutkan</label>
            <select id="f-sort" name="sort" class="field">
                <option value="featured" @selected(($f('sort') ?: 'featured') === 'featured')>Unggulan</option>
                <option value="price_asc" @selected($f('sort') === 'price_asc')>Harga termurah</option>
                <option value="price_desc" @selected($f('sort') === 'price_desc')>Harga termahal</option>
            </select>
        </div>
    @endif

    <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">Cari kendaraan</button>
        @if (! empty($filters))
            <a href="{{ $action }}" class="btn btn-outline">Reset filter</a>
        @endif
    </div>
</form>
