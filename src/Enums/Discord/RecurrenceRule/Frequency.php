<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule;

use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency;

enum Frequency: int
{
    case Yearly = 0;
    case Monthly = 1;
    case Weekly = 2;
    case Daily = 3;

    public function into(): RecurrenceFrequency
    {
        return RecurrenceFrequency::from(strtolower($this->name));
    }
}
