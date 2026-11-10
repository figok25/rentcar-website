<?php

namespace App\Support;

use App\Models\WebsiteSetting;

/** Saklar fitur opsional yang diatur admin di Pengaturan website. */
class Features
{
    public static function tours(): bool
    {
        return WebsiteSetting::bool('feature_tours_enabled', false);
    }

    public static function travel(): bool
    {
        return WebsiteSetting::bool('feature_travel_enabled', false);
    }
}
