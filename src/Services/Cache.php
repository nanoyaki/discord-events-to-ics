<?php

namespace Nanoyaki\DiscordEventsToIcs\Services;

class Cache
{
    public static function key($name): string
    {
        return str_replace(["\\", "/", "{", "}", "(", ")", "@", ":"], "", $name);
    }
}