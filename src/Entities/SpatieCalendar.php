<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\ExternalEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\GuildScheduledEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\RecurrenceRule;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\VoiceChannelEvent;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\EntityType;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Month;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\RecurrenceRule\Weekday;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceDay;
use Nanoyaki\DiscordEventsToIcs\Enums\RecurrenceFrequency;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Client;
use Spatie\IcalendarGenerator\Components\Calendar as ICalendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Components\Timezone;
use Spatie\IcalendarGenerator\Enums\RecurrenceMonth;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

readonly class SpatieCalendar implements CalendarInterface
{
    private ICalendar $calendar;

    /**
     * @param array<GuildScheduledEvent> $discordEvents
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

    public function discordEventToIcalEvent(GuildScheduledEvent $event): Event
    {
        $url = Client::DISCORD_EVENT_BASE_URI . "{$event->guildId}/{$event->id}";
        $organizer = $event->creator->globalName ?? $event->creator->username;

        $description = (
            !is_null($event->description) && $event->description !== ""
                ? $event->description . "\n"
                : ""
            )
            . "{$event->interestedMembers} interested members\n\n"
            . "Keep in mind that your client might have limitations "
            . "so Events might not be up to date at all times";


        $location = match (true) {
            $event instanceof VoiceChannelEvent =>
            "In voice channel: https://discord.com/channels/{$event->guildId}/{$event->channelId}",
            $event instanceof ExternalEvent =>
            "{$event->location}",
            default => ""
        };

        $calendarEvent = Event::create()
            ->name($event->name)
            ->url($url)
            ->organizer("", $organizer)
            ->addressName($location)
            ->description($description)
            ->startsAt($event->scheduledStartTime)
            ->endsAt($event->scheduledEndTime);

        if (!is_null($event->recurrenceRule)) {
            $rrule = $this->parseRecurrencyRule($event->recurrenceRule);
            $calendarEvent->rrule($rrule);
        }

        return $calendarEvent;
    }

    public function parseRecurrencyRule(RecurrenceRule $rrule): RRule
    {
        $recurrenceRule = RRule::frequency($rrule->frequency->into())
            ->starting($rrule->start)
            ->interval($rrule->interval);

        if (!is_null($rrule->count)) {
            $recurrenceRule->times($rrule->count);
        }

        if (!is_null($rrule->byWeekday)) {
            $recurrenceRule->weekdays = array_map(
                fn(Weekday $weekday) => $weekday->into(),
                $rrule->byWeekday
            );
        }

        if (!is_null($rrule->byMonth)) {
            $recurrenceRule->months = array_map(
                fn(Month $month) => $month->into(),
                $rrule->byMonth
            );
        }

        if (!is_null($rrule->byMonthDay)) {
            $recurrenceRule->onMonthDay($rrule->byMonthDay);
        }

        return $recurrenceRule;
    }

    public function result(): string
    {
        return $this->calendar->toString();
    }
}