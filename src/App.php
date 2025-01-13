<?php

namespace Nanoyaki\DiscordEventsToIcs;

use DateTimeImmutable;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\RecurrenceDay;
use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency;
use Spatie\IcalendarGenerator\Enums\RecurrenceMonth;
use Spatie\IcalendarGenerator\ValueObjects\RRule;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class App
{
    public const string DISCORD_API_BASE_URI = "https://discord.com/api/v10/";
    public const string DISCORD_EVENT_BASE_URI = "https://discord.com/events/";

    private readonly HttpClientInterface $client;
    private readonly Calendar $calendar;

    public function __construct(
        string $botToken
    ) {
        $this->client = HttpClient::create(
            [
                "base_uri" => self::DISCORD_API_BASE_URI,
                "headers" => [
                    "Authorization" => "Bot $botToken",
                    "Content-Type" => "application/json"
                ]
            ]
        );
        $this->calendar = Calendar::create("oh events");
    }

    public function getGuildScheduledEvents(string $guildId): array
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            "guilds/$guildId/scheduled-events"
        );

        return $response->toArray();
    }

    public function scheduledEventsToIcs(array $events): Response
    {
        array_walk($events, fn($event) => $this->calendar->event($this->eventToCalendarEvent($event)));

        return new Response($this->calendar->toString(), Response::HTTP_OK, [
            'Content-type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }

    public function eventToCalendarEvent(array $event): Event
    {
        $calendarEvent = Event::create()
            ->name($event["name"] ?? "Probably some movie Event")
            ->url(self::DISCORD_EVENT_BASE_URI . "{$event["guild_id"]}/{$event["id"]}")
            ->organizer("", ($event["creator"]["global_name"] ?? $event["creator"]["username"]) ?? "Unknown")
            ->startsAt(
                DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$event["scheduled_start_time"])
            )
            ->endsAt(
                DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$event["scheduled_end_time"])
            );

        $descriptionAdditive = match ((int)$event["entity_type"]) {
            1 => "In some stage on oh",
            2 => "In voice channel: https://discord.com/channels/{$event["guild_id"]}/{$event["channel_id"]}",
            3 => "Location: {$event["entity_metadata"]["location"]}"
        };
        $calendarEvent->description(($event["description"] ?? "") . $descriptionAdditive);

        if (!array_key_exists("recurrence_rule", $event)) {
            return $calendarEvent;
        }

        $eventRrule = $event["recurrence_rule"];
        $recurrenceRule = RRule::frequency(RecurrenceFrequency::from($eventRrule["frequency"]))
            ->starting(DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string)$eventRrule["start"]))
            ->interval((int)$eventRrule["interval"])
            ->times($eventRrule["count"]);

        if (!is_null($eventRrule["by_weekday"])) {
            $recurrenceRule->weekdays = array_map(fn($day) => RecurrenceDay::from($day), $eventRrule["by_weekday"]);
        }

        if (!is_null($eventRrule["by_month"])) {
            $recurrenceRule->months = array_map(fn($month) => RecurrenceMonth::from($month), $eventRrule["by_month"]);
        }

        if (!is_null($eventRrule["by_month_day"])) {
            $recurrenceRule->onMonthDay((int)$eventRrule["by_month_day"]);
        }

        $calendarEvent->rrule($recurrenceRule);

        return $calendarEvent;
    }
}
