<?php

namespace Nanoyaki\DiscordEventsToIcs\Services;

class Cache
{
    public static function key(string $name): string
    {
        return str_replace(["\\", "/", "{", "}", "(", ")", "@", ":"], "", $name);
    }
}