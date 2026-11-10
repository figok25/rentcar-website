<?php

namespace App\Support;

class Money
{
    /**
     * Format rupiah untuk tampilan, mis. 300000 -> "Rp 300.000".
     * Data tetap disimpan sebagai integer.
     */
    public static function rupiah(?int $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
