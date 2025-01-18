<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class ExternalEvent extends GuildScheduledEvent
{
    public string $location;

    /**
     * @param array<mixed> $apiEvent
     */
    public function __construct(array $apiEvent)
    {
        Validator::assert(
            "External event",
            Validator::isObject("entity_metadata", $apiEvent)
            && Validator::isString("location", $apiEvent["entity_metadata"])
        );

        $this->location = $apiEvent["entity_metadata"]["location"];

        parent::__construct($apiEvent);
    }
}