<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceDay;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceFrequency;
use Nanoyaki\DiscordEventsToIcs\Services\Discord;
use Spatie\IcalendarGenerator\Components\Calendar as ICalendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\RecurrenceMonth;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

readonly class Calendar
{
    private ICalendar $calendar;

    /**
     * @param array<mixed> $events
     * @throws \Exception
     */
    public function __construct(array $events)
    {
        $this->calendar = ICalendar::create("oh events")
            ->withoutTimezone();

        foreach ($events as $event) {
            $this->calendar->event($this->eventToCalendarEvent($event));
        }
    }

    /**
     * @param array<mixed> $event
     * @return Event
     * @throws \Exception
     */
    public function eventToCalendarEvent(array $event): Event
    {
        $entityType = $event['entity_type'];

        $startsAt = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            (string)$event["scheduled_start_time"]
        );
        assert($startsAt instanceof DateTimeImmutable);

        $calendarEvent = Event::create()
            ->name($event["name"] ?? "Probably some movie Event")
            ->url(Discord::DISCORD_EVENT_BASE_URI . "{$event["guild_id"]}/{$event["id"]}")
            ->organizer("", ($event["creator"]["global_name"] ?? $event["creator"]["username"]) ?? "Unknown")
            ->startsAt($startsAt);

        if ($entityType == 3 || !is_null($event["scheduled_end_time"])) {
            $endsAt = DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                (string)$event["scheduled_end_time"]
            );
            assert($endsAt instanceof DateTimeImmutable);

            $calendarEvent->endsAt($endsAt);
        }

        $description = "If you're using Google Calendar, some of " .
            "these values might be off by hours or even a day.\n\n";

        $description .= "Interested members: {$event["user_count"]}";

        $description .= match ((int)$entityType) {
            1, 2 => "\n\nIn voice channel: https://discord.com/channels/{$event["guild_id"]}/{$event["channel_id"]}",
            3 => "\n\nLocation: {$event["entity_metadata"]["location"]}",
            default => ""
        };

        $description .= ($event["description"] ?? "No description");

        $calendarEvent->description($description);

        if (!array_key_exists("recurrence_rule", $event) || is_null($event["recurrence_rule"])) {
            return $calendarEvent;
        }

        $calendarEvent->rrule($this->parseRecurrencyRule($event["recurrence_rule"]));

        return $calendarEvent;
    }

    /**
     * @param array<mixed> $eventRrule
     * @return RRule
     * @throws \Exception
     */
    public function parseRecurrencyRule(array $eventRrule): RRule
    {
        $startsAt = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            (string)$eventRrule["start"]
        );
        assert($startsAt instanceof DateTimeImmutable);

        $recurrenceRule = RRule::frequency(RecurrenceFrequency::from($eventRrule["frequency"])->into())
            ->starting($startsAt)
            ->interval((int)$eventRrule["interval"]);

        if (!is_null($eventRrule["count"])) {
            $recurrenceRule->times((int)$eventRrule["count"]);
        }

        if (!is_null($eventRrule["by_weekday"])) {
            foreach ($eventRrule["by_weekday"] as $weekday) {
                $recurrenceRule->onWeekDay(RecurrenceDay::from($weekday)->into());
            }
        }

        if (!is_null($eventRrule["by_month"])) {
            foreach ($eventRrule["by_month"] as $month) {
                $recurrenceRule->onMonth(RecurrenceMonth::from($month));
            }
        }

        if (!is_null($eventRrule["by_month_day"])) {
            $recurrenceRule->onMonthDay((int)$eventRrule["by_month_day"]);
        }

        return $recurrenceRule;
    }

    public function toString(): string
    {
        return $this->calendar->toString();
    }
}