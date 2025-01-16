<?php

namespace Nanoyaki\DiscordEventsToIcs\Services;

use DateTime;
use MongoDB\Collection;
use MongoDB\Client;

class CachedDiscord extends Discord
{
    private readonly Client $mongoClient;
    private readonly Collection $collection;

    public function __construct(string $token, string $mongoDbUri)
    {
        parent::__construct($token);

        $this->mongoClient = new Client(
            $mongoDbUri,
            [],
            [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ]
        );

        $this->collection = $this->mongoClient
            ->selectDatabase("discord")
            ->selectCollection("cache");
    }

    /**
     * @param string $guildId
     * @param bool $withUserCount
     * @return array<mixed>
     * @throws \Throwable
     */
    public function getScheduledEventsByGuild(string $guildId, bool $withUserCount = false): array
    {
        $cached = $this->getCached(__METHOD__);
        if (!is_null($cached)) {
            assert(is_array($cached));
            return $cached;
        }

        $result = parent::getScheduledEventsByGuild($guildId, $withUserCount);

        $this->cache(__METHOD__, $result);

        return $result;
    }

    private function getCached(string $method): mixed
    {
        $cachedValue = $this->collection->findOne(["method" => $method]);
        assert(is_null($cachedValue) || is_array($cachedValue));

        if (
            $cachedValue === null
            || new DateTime()->getTimestamp() - $cachedValue["timestamp"] >= 180
        ) {
            return null;
        }

        return $cachedValue["value"];
    }

    private function cache(string $method, mixed $value): void
    {
        $new = [
            "method" => $method,
            "value" => $value,
            "timestamp" => new DateTime()->getTimestamp()
        ];

        $current = $this->collection->findOneAndReplace(["method" => $method], $new);
        if ($current === null) {
            $this->collection->insertOne($new);
        }
    }
}