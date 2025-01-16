<?php

namespace Nanoyaki\DiscordEventsToIcs;

use Nanoyaki\DiscordEventsToIcs\Entities\Calendar;
use Nanoyaki\DiscordEventsToIcs\Services\CachedDiscord;
use Nanoyaki\DiscordEventsToIcs\Services\Discord;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;

class App
{
    private readonly Discord $discord;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            new Dotenv()->load(__DIR__ . '/../.env');
        }

        if (array_key_exists('MONGODB_URI', $_SERVER)) {
            $this->discord = new CachedDiscord($_SERVER["BOT_TOKEN"], $_SERVER["MONGODB_URI"]);
            return;
        }

        $this->discord = new Discord($_SERVER["BOT_TOKEN"]);
    }

    /**
     * @throws \Throwable
     */
    public function getCalendar(): Response
    {
        $discordEvents = $this->discord->getScheduledEventsByGuild($_SERVER["GUILD_ID"], true);
        $calendar = new Calendar($discordEvents);

        return new Response($calendar->toString(), Response::HTTP_OK, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }
}
