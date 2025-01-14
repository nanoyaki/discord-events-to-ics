<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums;

use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency as RFrequency;

enum RecurrenceFrequency: int
{
    case Yearly = 0;
    case Monthly = 1;
    case Weekly = 2;
    case Daily = 3;

    public function into(): RFrequency
    {
        return RFrequency::from(strtolower($this->name));
    }
}
