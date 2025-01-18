<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule;

use Spatie\IcalendarGenerator\Enums\RecurrenceDay;

enum Weekday: int
{
    case Monday = 0;
    case Tuesday = 1;
    case Wednesday = 2;
    case Thursday = 3;
    case Friday = 4;
    case Saturday = 5;
    case Sunday = 6;

    public function into(): RecurrenceDay
    {
        return RecurrenceDay::from(strtolower($this->name));
    }
}
