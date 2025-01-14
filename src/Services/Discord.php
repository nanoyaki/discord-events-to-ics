<?php

namespace Nanoyaki\DiscordEventsToIcs\Services;

use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Discord
{
    public const string DISCORD_API_BASE_URI = "https://discord.com/api/v10/";
    public const string DISCORD_EVENT_BASE_URI = "https://discord.com/events/";

    private readonly HttpClientInterface $client;

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
        );

        $result = $response->toArray();

        return $result;
    }
}