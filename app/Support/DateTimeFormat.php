<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;

class DateTimeFormat
{
    public static function display(?DateTimeInterface $dateTime, string $default = ''): string
    {
        if ($dateTime === null) {
            return $default;
        }

        return Date::instance($dateTime)
            ->timezone(config('app.display_timezone', 'Asia/Shanghai'))
            ->format('Y-m-d H:i:s');
    }
}
