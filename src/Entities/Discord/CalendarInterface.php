<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

interface CalendarInterface
{
    /**
     * @param array<mixed> $discordEvents
     */
    public function __construct(array $discordEvents);

    public function result(): mixed;
}