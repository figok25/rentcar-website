<?php

namespace App\Http\Requests;

use App\Enums\RentalServiceType;
use App\Services\WhatsAppMessageBuilder;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsAppInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vehicle' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:80'],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'service_type' => ['nullable', Rule::in(array_column(RentalServiceType::cases(), 'value'))],
            'pickup_location' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
            // Kolom jebakan untuk bot: pengguna asli tidak pernah mengisinya.
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
                    return;
                }

                if (WhatsAppMessageBuilder::days($this->input('start_date'), $this->input('end_date')) > WhatsAppMessageBuilder::MAX_DAYS) {
                    $validator->errors()->add('end_date', 'Durasi maksimal '.WhatsAppMessageBuilder::MAX_DAYS.' hari. Untuk sewa lebih lama, hubungi kami langsung.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'nama',
            'start_date' => 'tanggal ambil',
            'end_date' => 'tanggal kembali',
            'service_type' => 'jenis layanan',
            'pickup_location' => 'lokasi pengambilan',
            'notes' => 'catatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'max.string' => ':Attribute terlalu panjang (maksimal :max karakter).',
            'date_format' => ':Attribute tidak valid.',
            'start_date.after_or_equal' => 'Tanggal ambil tidak boleh sebelum hari ini.',
            'end_date.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal ambil.',
            'in' => ':Attribute tidak valid.',
            'website.max' => 'Permintaan tidak dapat diproses.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return url()->previous().'#form-sewa';
    }
}
