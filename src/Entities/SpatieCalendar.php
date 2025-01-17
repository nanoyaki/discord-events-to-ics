<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\EntityType;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceDay;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceFrequency;
use Nanoyaki\DiscordEventsToIcs\Services\Discord;
use Spatie\IcalendarGenerator\Components\Calendar as ICalendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Components\Timezone;
use Spatie\IcalendarGenerator\Enums\RecurrenceMonth;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

readonly class SpatieCalendar implements DiscordCalendarInterface
{
    private ICalendar $calendar;

    /**
     * @param array<mixed> $discordEvents
     * @throws \Exception
     */
    public function __construct(array $discordEvents)
    {
        $this->calendar = ICalendar::create("oh events")
            ->timezone(Timezone::create('UTC'));

        $this->calendar->event(
            array_map(
                fn($event) => $this->discordEventToIcalEvent($event),
                $discordEvents
            )
        );
    }

    /**
     * @param array<mixed> $event
     * @return Event
     * @throws \Exception
     */
    public function discordEventToIcalEvent(array $event): Event
    {
        $entityType = EntityType::from($event['entity_type']);

        $title = $event["name"] ?? "Probably some movie Event";
        $url = Discord::DISCORD_EVENT_BASE_URI . "{$event["guild_id"]}/{$event["id"]}";
        $organizer = ($event["creator"]["global_name"] ?? $event["creator"]["username"]) ?? "Unknown";

        $startsAt = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            (string)$event["scheduled_start_time"],
            new \DateTimeZone("UTC")
        );
        assert($startsAt instanceof DateTimeImmutable);

        $endTime = $startsAt->setTime(23, 59, 59);
        if ($entityType === EntityType::External || !is_null($event["scheduled_end_time"])) {
            $endTime = DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                (string)$event["scheduled_end_time"],
                new \DateTimeZone("UTC")
            );
            assert($endTime instanceof DateTimeImmutable);
        }

        $description = ($event["description"] ?? "No description")
            . "Interested members: {$event["user_count"]}\n\n"
            . "Keep in mind that your client might have limitations "
            . "so Events might not be up to date at all times";

        $location = match ($entityType) {
            EntityType::StageInstance, EntityType::Voice =>
            "In voice channel: https://discord.com/channels/{$event["guild_id"]}/{$event["channel_id"]}",
            EntityType::External =>
            "{$event["entity_metadata"]["location"]}"
        };

        $calendarEvent = Event::create()
            ->name($title)
            ->url($url)
            ->organizer("", $organizer)
            ->addressName($location)
            ->description($description)
            ->startsAt($startsAt)
            ->endsAt($endTime);

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
        $frequency = RecurrenceFrequency::from($eventRrule["frequency"])->into();
        $interval = (int)$eventRrule["interval"];

        $startsAt = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            (string)$eventRrule["start"]
        );
        assert($startsAt instanceof DateTimeImmutable);

        $recurrenceRule = RRule::frequency($frequency)
            ->starting($startsAt)
            ->interval($interval);

        if (!is_null($eventRrule["count"])) {
            $count = (int)$eventRrule["count"];
            $recurrenceRule->times($count);
        }

        if (!is_null($eventRrule["by_weekday"])) {
            foreach ($eventRrule["by_weekday"] as $weekday) {
                $weekday = RecurrenceDay::from($weekday)->into();
                $recurrenceRule->onWeekDay($weekday);
            }
        }

        if (!is_null($eventRrule["by_month"])) {
            foreach ($eventRrule["by_month"] as $month) {
                $month = RecurrenceMonth::from($month);
                $recurrenceRule->onMonth($month);
            }
        }

        if (!is_null($eventRrule["by_month_day"])) {
            $monthDay = (int)$eventRrule["by_month_day"];
            $recurrenceRule->onMonthDay($monthDay);
        }

        return $recurrenceRule;
    }

    public function result(): string
    {
        return $this->calendar->toString();
    }
}