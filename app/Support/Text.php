<?php

namespace App\Support;

class Text
{
    /**
     * Pecah teks bebas menjadi baris-baris poin: kosong dibuang,
     * awalan "-", "*" atau "•" dihapus.
     *
     * @return array<int, string>
     */
    public static function lines(?string $text): array
    {
        $lines = preg_split('/\R/u', (string) $text) ?: [];

        return array_values(array_filter(array_map(
            fn (string $line) => trim(preg_replace('/^\s*[-*•]\s*/u', '', $line)),
            $lines
        ), fn (string $line) => $line !== ''));
    }
}
