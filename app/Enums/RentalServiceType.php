<?php

namespace App\Enums;

enum RentalServiceType: string
{
    case SelfDrive = 'self_drive';
    case WithDriver = 'with_driver';

    public function label(): string
    {
        return match ($this) {
            self::SelfDrive => 'Lepas kunci',
            self::WithDriver => 'Dengan driver',
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
