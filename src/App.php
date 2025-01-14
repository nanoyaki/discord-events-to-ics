<?php

namespace Nanoyaki\DiscordEventsToIcs;

use Nanoyaki\DiscordEventsToIcs\Services\CachedDiscord;
use Nanoyaki\DiscordEventsToIcs\Services\Calendar;
use Nanoyaki\DiscordEventsToIcs\Services\Discord;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;

class App
{
    private readonly Discord $discord;

    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');

        $this->discord = new CachedDiscord($_ENV["BOT_TOKEN"], $_ENV["MONGODB_URI"]);
    }

    public function getCalendar(): Response
    {
        $discordEvents = $this->discord->getScheduledEventsByGuild($_ENV["GUILD_ID"], true);
        $calendar = new Calendar($discordEvents);

        return new Response($calendar->toString(), Response::HTTP_OK, [
            'Content-type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }
}
