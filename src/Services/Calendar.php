<?php

namespace Nanoyaki\DiscordEventsToIcs\Services;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceDay;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceFrequency;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Components\Timezone;
use Spatie\IcalendarGenerator\Enums\RecurrenceMonth;
use Spatie\IcalendarGenerator\ValueObjects\RRule;
use Spatie\IcalendarGenerator\Components\Calendar as ICalendar;

readonly class Calendar
{
    private ICalendar $calendar;

    public function __construct(array $events)
    {
        $this->calendar = ICalendar::create("oh events")
            ->timezone(Timezone::create("Europe/Berlin"));

        foreach ($events as $event) {
            $this->calendar->event($this->eventToCalendarEvent($event));
        }
    }

    public function eventToCalendarEvent(array $event): Event
    {
        $entityType = $event['entity_type'];

        $calendarEvent = Event::create()
            ->name($event["name"] ?? "Probably some movie Event")
            ->url(Discord::DISCORD_EVENT_BASE_URI . "{$event["guild_id"]}/{$event["id"]}")
            ->organizer("", ($event["creator"]["global_name"] ?? $event["creator"]["username"]) ?? "Unknown")
            ->startsAt(
                DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$event["scheduled_start_time"])
            );

        if ($entityType == 3 || !is_null($event["scheduled_end_time"])) {
            $calendarEvent->endsAt(
                DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$event["scheduled_end_time"])
            );
        }

        $description = "If you're using Google Calendar, some of " .
            "these values might be off by hours or even a day.\n\n";

        $description .= "Interested members: {$event["user_count"]}";

        $description .= match ((int)$entityType) {
            1, 2 => "\n\nIn voice channel: https://discord.com/channels/{$event["guild_id"]}/{$event["channel_id"]}",
            3 => "\n\nLocation: {$event["entity_metadata"]["location"]}"
        };

        $description .= ($event["description"] ?? "No description");

        $calendarEvent->description($description);

        if (!array_key_exists("recurrence_rule", $event)) {
            return $calendarEvent;
        }

        $calendarEvent->rrule($this->parseRecurrencyRule($event["recurrence_rule"]));

        return $calendarEvent;
    }

    public function parseRecurrencyRule(array $eventRrule): RRule
    {
        $recurrenceRule = RRule::frequency(RecurrenceFrequency::from($eventRrule["frequency"])->into())
            ->starting(DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$eventRrule["start"]))
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