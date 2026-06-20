<?php

namespace App\Models\Concerns;

use App\Support\DateTimeFormat;
use DateTimeInterface;

trait SerializesDisplayDates
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return DateTimeFormat::display($date);
    }
}
