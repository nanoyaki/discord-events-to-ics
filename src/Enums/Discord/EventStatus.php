<?php

namespace Nanoyaki\DiscordEventsToIcs\Enums\Discord;

enum EventStatus: int
{
    case Scheduled = 1;
    case Active = 2;
    case Completed = 3;
    case Canceled = 4;
}