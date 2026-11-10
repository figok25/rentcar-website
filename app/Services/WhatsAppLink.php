<?php

namespace App\Services;

use App\Models\WebsiteSetting;

/**
 * Tautan wa.me sederhana untuk tombol konsultasi.
 * Form rencana sewa lengkap (tanggal, layanan, dst.) dibuat di Phase 4.
 */
class WhatsAppLink
{
    /**
     * Nomor admin dari pengaturan CMS (angka saja, format internasional),
     * atau null jika belum diisi / tidak valid.
     */
    public static function number(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) WebsiteSetting::get('whatsapp_number', ''));

        return preg_match('/^[1-9]\d{7,14}$/', $number) ? $number : null;
    }

    public static function url(?string $message = null): ?string
    {
        $number = self::number();

        if ($number === null) {
            return null;
        }

        $url = 'https://wa.me/'.$number;

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
