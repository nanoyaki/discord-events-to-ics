<?php

namespace Nanoyaki\DiscordEventsToIcs\Services\Discord;

use Nanoyaki\DiscordEventsToIcs\Entities\Discord\ExternalEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\GuildScheduledEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\VoiceChannelEvent;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\EntityType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class Client
{
    public const string DISCORD_API_BASE_URI = "https://discord.com/api/v10/";
    public const string DISCORD_EVENT_BASE_URI = "https://discord.com/events/";

    private HttpClientInterface $client;

    public function __construct(
        string $botToken
    ) {
        $this->client = HttpClient::create(
            [
                "base_uri" => self::DISCORD_API_BASE_URI,
                "headers" => [
                    "Authorization" => "Bot $botToken",
                    "Content-Type" => "application/json"
                ]
            ]
        );
    }

    /**
     * @param string $guildId
     * @param bool $withUserCount
     * @return array<GuildScheduledEvent>
     * @throws \Throwable a bunch of symfony errors
     */
    public function getScheduledEventsByGuild(string $guildId, bool $withUserCount = false): array
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            "guilds/$guildId/scheduled-events",
            [
                "query" => [
                    "with_user_count" => $withUserCount,
                ],
            ],
        )->toArray();

        return array_map(
            function ($apiEvent): GuildScheduledEvent {
                Validator::assert(
                    "Entity type",
                    Validator::isInt("entity_type", $apiEvent)
                );

                return match (EntityType::from($apiEvent["entity_type"])) {
                    EntityType::StageInstance, EntityType::Voice => new VoiceChannelEvent($apiEvent),
                    EntityType::External => new ExternalEvent($apiEvent)
                };
            },
            $response
        );
    }
}