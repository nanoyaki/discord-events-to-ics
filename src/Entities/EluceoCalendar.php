<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities;

use DateTimeImmutable as PhpDateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\EntityType;
use Nanoyaki\DiscordEventsToIcs\Services\Discord;

class EluceoCalendar implements DiscordCalendarInterface
{
    private Calendar $calendar;

    /**
     * Validate that the events don't have recurrence rules
     * before using this class
     *
     * @param array<mixed> $discordEvents
     */
    public function __construct(array $discordEvents)
    {
        assert(
            array_all(
                $discordEvents,
                fn($event) => !array_key_exists("recurrence_rule", $event) || is_null($event["recurrence_rule"])
            )
        );

        $icalEvents = array_map(
            fn($event) => $this->discordEventToIcalEvent($event),
            $discordEvents
        );

        $firstTimeSpan = $icalEvents[0]->getOccurrence();
        assert($firstTimeSpan instanceof TimeSpan);
        $firstDate = $firstTimeSpan->getBegin()->getDateTime();

        $lastElement = count($icalEvents) - 1;
        $lastTimeSpan = $icalEvents[$lastElement]->getOccurrence();
        assert($lastTimeSpan instanceof TimeSpan);
        $lastDate = $lastTimeSpan->getEnd()->getDateTime();

        $timezone = TimeZone::createFromPhpDateTimeZone(
            new \DateTimeZone("UTC"),
            $firstDate,
            $lastDate
        );

        $this->calendar = new Calendar($icalEvents)
            ->addTimeZone($timezone);
    }

    /**
     * This version of eluceo/ical currently does not support
     * recurrence rules. Only use this if the calendar does not
     * have a single recurrence rule.
     *
     * @param array<mixed> $event
     * @return Event
     */
    public function discordEventToIcalEvent(array $event): Event
    {
        $entityType = EntityType::from($event['entity_type']);

        $title = $event["name"] ?? "Probably some movie Event";
        $url = new Uri(Discord::DISCORD_EVENT_BASE_URI . "{$event["guild_id"]}/{$event["id"]}");
        $organizer = new Organizer(
            new EmailAddress("not-a@real-email.com"),
            ($event["creator"]["global_name"] ?? $event["creator"]["username"]) ?? "Unknown"
        );

        $startTime = PhpDateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            (string)$event["scheduled_start_time"],
            new \DateTimeZone("UTC")
        );
        assert($startTime instanceof PhpDateTimeImmutable);

        $approxEndTime = $startTime->setTime(23, 59, 59);

        $occurrence = new TimeSpan(
            new DateTime($startTime, true),
            new DateTime($approxEndTime, true)
        );

        if ($entityType === EntityType::External || !is_null($event["scheduled_end_time"])) {
            $endTime = PhpDateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                (string)$event["scheduled_end_time"],
                new \DateTimeZone("UTC")
            );
            assert($endTime instanceof PhpDateTimeImmutable);

            $occurrence = new TimeSpan(
                new DateTime($startTime, true),
                new DateTime($endTime, true)
            );
        }

        $location = new Location(
            match ($entityType) {
                EntityType::StageInstance, EntityType::Voice =>
                "In voice channel: https://discord.com/channels/{$event["guild_id"]}/{$event["channel_id"]}",
                EntityType::External =>
                "{$event["entity_metadata"]["location"]}"
            }
        );

        $description = ($event["description"] ?? "No description")
            . "\nInterested members: {$event["user_count"]}\n\n"
            . "Keep in mind that your client might have limitations "
            . "so Events might not be up to date at all times";

        $touched = new Timestamp(
            new \DateTime(
                "now",
                new \DateTimeZone("Europe/Berlin")
            )
        );

        return new Event()
            ->setSummary($title)
            ->setUrl($url)
            ->setOrganizer($organizer)
            ->setOccurrence($occurrence)
            ->setDescription($description)
            ->setLocation($location)
            ->touch($touched);
    }

    public function result(): Component
    {
        return new CalendarFactory()->createCalendar($this->calendar);
    }
}