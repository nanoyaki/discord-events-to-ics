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
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');

        if (array_key_exists('MONGODB_URI', $_ENV)) {
            $this->discord = new CachedDiscord($_ENV["BOT_TOKEN"], $_ENV["MONGODB_URI"]);
            return;
        }

        $this->discord = new Discord($_ENV["BOT_TOKEN"]);
    }

    /**
     * @throws \Throwable
     */
    public function getCalendar(): Response
    {
        $discordEvents = $this->discord->getScheduledEventsByGuild($_ENV["GUILD_ID"], true);
        $calendar = new Calendar($discordEvents);

        return new Response($calendar->toString(), Response::HTTP_OK, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }
}
