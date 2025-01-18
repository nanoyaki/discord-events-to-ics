<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Frequency;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Month;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Weekday;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class RecurrenceRule
{
    public DateTimeImmutable $start;

    public ?DateTimeImmutable $end;

    public Frequency $frequency;

    public int $interval;

    /**
     * @var ?array<Weekday> $byWeekday
     */
    public ?array $byWeekday;

    /**
     * @var ?array<NthWeekday> $byNthWeekday
     */
    public ?array $byNthWeekday;

    /**
     * @var ?array<Month> $byMonth
     */
    public ?array $byMonth;

    /**
     * @var ?array<int> $byMonthDay
     */
    public ?array $byMonthDay;

    /**
     * @var ?array<int> $byYearDay
     */
    public ?array $byYearDay;

    public ?int $count;

    public function __construct(array $apiRecurrenceRule)
    {
        Validator::assert(
            "Recurrence rule",
            Validator::isString("start", $apiRecurrenceRule),
            Validator::isNullableString("end", $apiRecurrenceRule),
            Validator::isNullableInt("frequency", $apiRecurrenceRule),
            Validator::isInt("interval", $apiRecurrenceRule),
            Validator::isNullableArray("by_weekday", $apiRecurrenceRule),
            Validator::isNullableArray("by_n_weekday", $apiRecurrenceRule),
            Validator::isNullableArray("by_month", $apiRecurrenceRule),
            Validator::isNullableArray("by_month_day", $apiRecurrenceRule),
            Validator::isNullableArray("by_year_day", $apiRecurrenceRule),
            Validator::isNullableInt("count", $apiRecurrenceRule),
        );

        $this->start = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $apiRecurrenceRule["start"],
            new \DateTimeZone("UTC")
        );
        $this->end = !is_null($apiRecurrenceRule["end"])
            ? DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                $apiRecurrenceRule["end"],
                new \DateTimeZone("UTC")
            )
            : null;
        $this->frequency = !is_null($apiRecurrenceRule["frequency"])
            ? Frequency::from($apiRecurrenceRule["frequency"])
            : null;
        $this->interval = $apiRecurrenceRule["interval"];
        $this->byWeekday = !is_null($apiRecurrenceRule["by_weekday"])
            ? array_map(
                fn($int) => Weekday::from($int),
                $apiRecurrenceRule["by_weekday"]
            )
            : null;
        $this->byNthWeekday = !is_null($apiRecurrenceRule["by_n_weekday"])
            ? array_map(
                fn($nthWeekday) => new NthWeekday($nthWeekday),
                $apiRecurrenceRule["by_n_weekday"]
            )
            : null;
        $this->byMonth = !is_null($apiRecurrenceRule["by_month"])
            ? array_map(
                fn($int) => Month::from($int),
                $apiRecurrenceRule["by_month"]
            )
            : null;
        $this->byMonthDay = $apiRecurrenceRule["by_month_day"];
        $this->byYearDay = $apiRecurrenceRule["by_year_day"];
        $this->count = $apiRecurrenceRule["count"];
    }
}