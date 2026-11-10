<?php

namespace App\Http\Controllers;

use App\Http\Requests\WhatsAppInquiryRequest;
use App\Services\VehicleCatalog;
use App\Services\WhatsAppLink;
use App\Services\WhatsAppMessageBuilder;
use Illuminate\Http\RedirectResponse;

/**
 * Menyiapkan pesan WhatsApp dari rencana sewa. Tidak ada data yang disimpan:
 * pengunjung diarahkan ke wa.me dengan teks siap kirim.
 */
class InquiryController extends Controller
{
    public function store(WhatsAppInquiryRequest $request, VehicleCatalog $catalog, WhatsAppMessageBuilder $builder): RedirectResponse
    {
        $data = $request->validated();

        $vehicle = $catalog->visible()
            ->with('activeRates')
            ->where('slug', $data['vehicle'])
            ->firstOrFail();

        $types = $vehicle->activeRates->map(fn ($rate) => $rate->service_type->value);

        if (! empty($data['service_type']) && $types->isNotEmpty() && ! $types->contains($data['service_type'])) {
            return back()->withInput()->withErrors(['service_type' => 'Jenis layanan tidak tersedia untuk unit ini.'])->withFragment('form-sewa');
        }

        $url = WhatsAppLink::url($builder->build($vehicle, $data));

        if ($url === null) {
            return back()->withInput()->withErrors(['whatsapp' => 'Nomor WhatsApp admin belum tersedia. Silakan coba lagi nanti.'])->withFragment('form-sewa');
        }

        return redirect()->away($url);
    }
}
