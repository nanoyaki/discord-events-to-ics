<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums\Discord;

enum EntityType: int
{
    case StageInstance = 1;
    case Voice = 2;
    case External = 3;
}
