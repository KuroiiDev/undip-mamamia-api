<?php

namespace App\Support;

class DistanceFormatter
{
    /**
     * Format distance in meters to a human-readable label.
     *
     * @param  float|null  $meters
     * @return string|null
     */
    public static function format(?float $meters): ?string
    {
        if ($meters === null) {
            return null;
        }

        if ($meters < 1000) {
            return (int) round($meters).' m';
        }

        return number_format($meters / 1000, 1, '.', '').' km';
    }
}
