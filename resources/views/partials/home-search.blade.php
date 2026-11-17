{{-- Pencarian ringkas di beranda. Mengirim ke katalog dengan parameter filter yang sama. Variabel: $options --}}
<form method="GET" action="{{ route('vehicles.index') }}" role="search" aria-label="Cari kendaraan" class="card filter-card p-5 sm:p-7">
    <p class="flex items-center gap-3 text-xl font-extrabold text-ink mb-5">
        <span class="grid size-9 place-items-center rounded-xl bg-brand-soft text-brand" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
        </span>
        Temukan unit yang cocok
    </p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto] lg:items-end">
        <div>
            <label for="h-q" class="block mb-1.5">Cari kendaraan</label>
            <input id="h-q" type="search" name="q" maxlength="60" placeholder="Nama, merek, atau model" class="field">
        </div>

        <div>
            <label for="h-category" class="block mb-1.5">Kategori</label>
            <select id="h-category" name="category" class="field">
                <option value="">Semua kategori</option>
                @foreach ($options['categories'] as $category)
                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="h-brand" class="block mb-1.5">Merek</label>
            <select id="h-brand" name="brand" class="field">
                <option value="">Semua merek</option>
                @foreach ($options['brands'] as $brand)
                    <option value="{{ $brand }}">{{ $brand }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="h-cap" class="block mb-1.5">Kapasitas</label>
            <select id="h-cap" name="min_capacity" class="field">
                <option value="">Berapa saja</option>
                @foreach (\App\Services\VehicleCatalog::CAPACITY_OPTIONS as $cap)
                    <option value="{{ $cap }}">{{ $cap }} penumpang atau lebih</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-accent sm:col-span-2 lg:col-span-1 lg:min-h-11 px-7">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            Cari kendaraan
        </button>
    </div>

    <p class="mt-4 text-sm text-road-soft">
        Perlu filter lain seperti transmisi atau harga?
        <a href="{{ route('vehicles.index') }}" class="font-semibold text-brand underline underline-offset-4">Buka katalog lengkap</a>
    </p>
</form>
