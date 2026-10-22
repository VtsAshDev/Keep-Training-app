<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Short Portuguese date labels used across the screens.
 */
final class Dates
{
    /**
     * "quinta", without the "-feira".
     */
    public static function weekday(CarbonInterface $date): string
    {
        return Str::before($date->locale(app()->getLocale())->isoFormat('dddd'), '-');
    }

    /**
     * "Quinta, 22 out".
     */
    public static function long(CarbonInterface $date): string
    {
        return Str::ucfirst(self::weekday($date)).', '.$date->locale(app()->getLocale())->isoFormat('D MMM');
    }

    /**
     * "hoje", "ontem", the weekday within the last week, "semana passada", or "22 out".
     */
    public static function relative(CarbonInterface $date): string
    {
        $days = (int) $date->copy()->startOfDay()->diffInDays(now()->startOfDay());

        return match (true) {
            $days === 0 => __('today'),
            $days === 1 => __('yesterday'),
            $days < 7 => self::weekday($date),
            $days < 14 => __('last week'),
            default => $date->locale(app()->getLocale())->isoFormat('D MMM'),
        };
    }
}
