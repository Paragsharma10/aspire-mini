<?php

namespace App\Helpers;


use DateTimeInterface;

class TimeHelper
{
    /**
     * @return DateTimeInterface
     */
    public static function toLocalTime(DateTimeInterface $time, $format = 'c')
    {
        $timezone = optional(auth()->user())->timezone ?? config('app.timezone');
        return $time->timezone($timezone)->format($format);
    }
}
