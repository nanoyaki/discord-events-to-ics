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
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\ExternalEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\GuildScheduledEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\VoiceChannelEvent;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Client;

class EluceoCalendar implements CalendarInterface
{
    private Calendar $calendar;

    /**
     * Validate that the events don't have recurrence rules
     * before using this class
     *
     * @param array<GuildScheduledEvent> $discordEvents
     */
    public function __construct(array $discordEvents)
    {
        assert(
            array_all(
                $discordEvents,
                fn(GuildScheduledEvent $event) => is_null($event->recurrenceRule)
            )
        );

        $icalEvents = array_map(
            fn(GuildScheduledEvent $event) => $this->discordEventToIcalEvent($event),
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
     * @param GuildScheduledEvent $event
     * @return Event
     */
    public function discordEventToIcalEvent(GuildScheduledEvent $event): Event
    {
        $url = new Uri(Client::DISCORD_EVENT_BASE_URI . "{$event->guildId}/{$event->id}");
        $organizer = new Organizer(
            new EmailAddress("not-a@real-email.com"),
            $event->creator->globalName ?? $event->creator->username
        );

        $description = (
            !is_null($event->description) && $event->description !== ""
                ? $event->description . "\n"
                : ""
            )
            . "{$event->interestedMembers} interested members\n\n"
            . "Keep in mind that your client might have limitations "
            . "so Events might not be up to date at all times";

        $location = new Location(
            match (true) {
                $event instanceof VoiceChannelEvent =>
                "In voice channel: https://discord.com/channels/{$event->guildId}/{$event->channelId}",
                $event instanceof ExternalEvent =>
                "{$event->location}",
                default => ""
            }
        );

        $occurrence = new TimeSpan(
            new DateTime($event->scheduledStartTime, true),
            new DateTime($event->scheduledEndTime, true)
        );

        $touched = new Timestamp(
            new PhpDateTimeImmutable(
                "now",
                new \DateTimeZone("Europe/Berlin")
            )
        );

        return new Event()
            ->setSummary($event->name)
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