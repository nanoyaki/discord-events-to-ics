<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Weekday;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class NthWeekday
{
    public int $monthWeek;

    public Weekday $weekday;

    /**
     * @param array<mixed> $apiNthWeekday
     */
    public function __construct(array $apiNthWeekday)
    {
        Validator::assert(
            "Nth weekday",
            Validator::isInt("n", $apiNthWeekday),
            Validator::isInt("day", $apiNthWeekday)
        );

        $this->monthWeek = $apiNthWeekday["n"];
        $this->weekday = Weekday::from($apiNthWeekday["day"]);
    }
}