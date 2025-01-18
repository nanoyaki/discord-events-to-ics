<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class VoiceChannelEvent extends GuildScheduledEvent
{
    public string $channelId;

    /**
     * @param array<mixed> $apiEvent
     */
    public function __construct(array $apiEvent)
    {
        Validator::assert(
            "Voice channel event",
            Validator::isString("channel_id", $apiEvent)
        );

        $this->channelId = $apiEvent["channel_id"];

        parent::__construct($apiEvent);
    }
}