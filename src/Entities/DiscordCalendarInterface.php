<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities;

interface DiscordCalendarInterface
{
    /**
     * @param array<mixed> $discordEvents
     */
    public function __construct(array $discordEvents);

    public function result(): mixed;
}