<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums;

use Spatie\IcalendarGenerator\Enums\RecurrenceDay as RDay;

enum RecurrenceDay: int
{
    case Monday = 0;
    case Tuesday = 1;
    case Wednesday = 2;
    case Thursday = 3;
    case Friday = 4;
    case Saturday = 5;
    case Sunday = 6;

    public function into(): RDay
    {
        return RDay::from(strtolower($this->name));
    }
}
