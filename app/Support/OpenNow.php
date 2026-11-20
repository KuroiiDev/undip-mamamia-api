<?php

namespace App\Support;

use App\Models\OpeningHour;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OpenNow
{
    /**
     * Check if the restaurant is currently open based on its opening hours.
     *
     * @param  Collection<int, OpeningHour>  $openingHours
     * @param  string  $timezone
     * @return bool
     */
    public static function check(Collection $openingHours, string $timezone = 'Asia/Jakarta'): bool
    {
        $now = Carbon::now($timezone);
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday

        $todayHours = $openingHours->firstWhere('day_of_week', $dayOfWeek);

        if (! $todayHours || $todayHours->is_closed) {
            return false;
        }

        if ($todayHours->open_time === null || $todayHours->close_time === null) {
            return false;
        }

        $openTime = Carbon::parse($todayHours->open_time, $timezone);
        $closeTime = Carbon::parse($todayHours->close_time, $timezone);

        // Handle overnight hours (e.g., 22:00 - 02:00)
        if ($closeTime->lte($openTime)) {
            return $now->gte($openTime) || $now->lte($closeTime);
        }

        return $now->between($openTime, $closeTime);
    }
}
