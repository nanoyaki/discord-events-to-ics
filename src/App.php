<?php

namespace Nanoyaki\DiscordEventsToIcs;

use Nanoyaki\DiscordEventsToIcs\Entities\Discord\GuildScheduledEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\EluceoCalendar;
use Nanoyaki\DiscordEventsToIcs\Entities\SpatieCalendar;
use Nanoyaki\DiscordEventsToIcs\Services\Cache;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

readonly class App
{
    private Client $discord;

    private FilesystemAdapter $cache;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            new Dotenv()->load(__DIR__ . '/../.env');
        }

        $this->discord = new Client($_SERVER["BOT_TOKEN"]);

        $this->cache = new FilesystemAdapter(
            "discord",
            180,
            $_SERVER["CACHE_DIR"] ?? __DIR__ . "/../cache"
        );
    }

    /**
     * @throws \Throwable
     */
    public function getCalendar(): Response
    {
        $calendar = $this->cache->get(
            Cache::key(__NAMESPACE__ . __CLASS__ . __METHOD__),
            function (ItemInterface $item): string {
                $item->expiresAfter(180);

                $discordEvents = $this->discord->getScheduledEventsByGuild($_SERVER["GUILD_ID"], true);

                $hasRecurrenceRules = array_any(
                    $discordEvents,
                    fn(GuildScheduledEvent $event) => !is_null($event->recurrenceRule)
                );

                $calendar = $hasRecurrenceRules
                    ? new SpatieCalendar($discordEvents)
                    : new EluceoCalendar($discordEvents);

                return (string)$calendar->result();
            }
        );

        return new Response($calendar, Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }
}
