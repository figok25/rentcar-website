{{-- Form rencana sewa -> WhatsApp. Variabel: $vehicle (dengan activeRates) --}}
@php
    $rates = $vehicle->activeRates->sortBy(fn ($r) => $r->service_type->value)->values();
    $today = now()->format('Y-m-d');
    $err = fn (string $key) => $errors->first($key);
@endphp
<form id="form-sewa" method="POST" action="{{ route('inquiry.store') }}" novalidate
      class="card p-5 scroll-mt-24"
      x-data="{
          start: @js(old('start_date', '')),
          end: @js(old('end_date', '')),
          get days() {
              if (!this.start || !this.end) return null;
              const d = Math.round((new Date(this.end) - new Date(this.start)) / 86400000);
              return d < 0 ? null : Math.max(1, d);
          }
      }">
    @csrf
    <input type="hidden" name="vehicle" value="{{ $vehicle->slug }}">

    <h3 class="text-2xl">Rencanakan sewa</h3>
    <p class="text-sm text-road-soft mt-1">Isi rencana Anda, lalu kirim lewat WhatsApp. Ketersediaan dan harga final dikonfirmasi admin; belum ada pemesanan otomatis.</p>

    @if ($errors->any())
        <div role="alert" class="mt-3 rounded-lg border border-sign-red/40 bg-red-50 text-sign-red text-sm p-3">
            <p class="font-semibold">Mohon periksa isian Anda:</p>
            <ul class="list-disc pl-5 mt-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <div>
            <label for="f-start" class="block text-sm font-semibold mb-1">Tanggal ambil</label>
            <input id="f-start" name="start_date" type="date" class="field" min="{{ $today }}" required
                   x-model="start" @change="if (end && end < start) end = start" aria-invalid="{{ $err('start_date') ? 'true' : 'false' }}">
        </div>
        <div>
            <label for="f-end" class="block text-sm font-semibold mb-1">Tanggal kembali</label>
            <input id="f-end" name="end_date" type="date" class="field" min="{{ $today }}" required
                   x-model="end" :min="start || '{{ $today }}'" aria-invalid="{{ $err('end_date') ? 'true' : 'false' }}">
        </div>
    </div>
    <p class="mt-2 text-sm text-road-soft" x-show="days" x-cloak>Durasi: <strong x-text="days + ' hari'"></strong></p>

    @if ($rates->isNotEmpty())
        <div class="mt-3">
            <label for="f-service" class="block text-sm font-semibold mb-1">Jenis layanan</label>
            <select id="f-service" name="service_type" class="field">
                @if ($rates->count() > 1)
                    <option value="">Belum menentukan</option>
                @endif
                @foreach ($rates as $rate)
                    <option value="{{ $rate->service_type->value }}" @selected(old('service_type', $rates->count() === 1 ? $rate->service_type->value : '') === $rate->service_type->value)>
                        {{ $rate->service_type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="mt-3">
        <label for="f-name" class="block text-sm font-semibold mb-1">Nama Anda</label>
        <input id="f-name" name="customer_name" type="text" class="field" maxlength="80" required autocomplete="name"
               value="{{ old('customer_name') }}" aria-invalid="{{ $err('customer_name') ? 'true' : 'false' }}">
    </div>

    <div class="mt-3">
        <label for="f-pickup" class="block text-sm font-semibold mb-1">Lokasi pengambilan <span class="font-normal text-road-soft">(opsional)</span></label>
        <input id="f-pickup" name="pickup_location" type="text" class="field" maxlength="150" value="{{ old('pickup_location') }}">
    </div>

    <div class="mt-3">
        <label for="f-notes" class="block text-sm font-semibold mb-1">Catatan <span class="font-normal text-road-soft">(opsional)</span></label>
        <textarea id="f-notes" name="notes" rows="3" maxlength="500" class="field">{{ old('notes') }}</textarea>
    </div>

    {{-- Kolom jebakan bot: disembunyikan dari pengguna dan pembaca layar. --}}
    <div class="absolute -left-[9999px]" aria-hidden="true">
        <label for="f-website">Jangan diisi</label>
        <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off" value="">
    </div>

    <button type="submit" class="btn btn-wa w-full mt-4">Kirim lewat WhatsApp</button>
</form>
