<?php

namespace App\Enums;

enum TransmissionType: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Automatic => 'Matic',
            self::Other => 'Lainnya',
        };
    }

    /**
     * @return array<string, string> value => label
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
