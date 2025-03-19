<?php

namespace Nanoyaki\DiscordEventsToIcs;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nanoyaki\DiscordEventsToIcs\Entities\Discord\GuildScheduledEvent;
use Nanoyaki\DiscordEventsToIcs\Entities\EluceoCalendar;
use Nanoyaki\DiscordEventsToIcs\Entities\SpatieCalendar;
use Nanoyaki\DiscordEventsToIcs\Services\Cache;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Client;
use Psr\Log\LogLevel;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

readonly class App
{
    public const string PACKAGE_NAME = "discord-events-to-ics";

    private Client $discord;

    private FilesystemAdapter $cache;

    private Logger $logger;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            new Dotenv()->load(__DIR__ . '/../.env');
        }

        $this->discord = new Client($_SERVER["BOT_TOKEN"]);

        $this->cache = new FilesystemAdapter(
            "discord",
            180,
            $_SERVER["CACHE_DIR"] ?? __DIR__ . "/../var/cache"
        );

        $packageName = self::PACKAGE_NAME;
        $logPath = ($_SERVER["LOG_PATH"] ?? __DIR__ . '/../var/log') . "/$packageName.log";
        $logLevel = match ($_SERVER["LOG_LEVEL"]) {
            "critical" => LogLevel::CRITICAL,
            "warning" => LogLevel::WARNING,
            "info" => LogLevel::INFO,
            default => LogLevel::ERROR,
        };

        $this->logger = new Logger($packageName);
        $this->logger->pushHandler(new StreamHandler($logPath, $logLevel));
    }

    public function run(): Response
    {
        try {
            $this->logger->info("Processing incoming request");

            $calendar = $this->getCalendar();

            $this->logger->info("Request processed, sending response");

            return $calendar;
        } catch (\Throwable $exception) {
            $this->logger->critical($exception->getMessage(), [
                "stackTrace" => $exception->getTraceAsString(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine()
            ]);

            return new Response(
                "An internal error occurred",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [
                    "Content-Type" => "text/plain; charset=utf-8"
                ]
            );
        }
    }

    /**
     * @throws \Throwable
     */
    public function getCalendar(): Response
    {
        $calendar = $this->cache->get(
            Cache::key(__NAMESPACE__ . __CLASS__ . __METHOD__),
            function (ItemInterface $item): string {
                $item->expiresAfter(180);

                $discordEvents = $this->discord->getScheduledEventsByGuild($_SERVER["GUILD_ID"], true);

                $hasRecurrenceRules = array_any(
                    $discordEvents,
                    fn(GuildScheduledEvent $event) => !is_null($event->recurrenceRule)
                );

                $calendar = $hasRecurrenceRules
                    ? new SpatieCalendar($discordEvents)
                    : new EluceoCalendar($discordEvents);

                return (string)$calendar->result();
            }
        );

        return new Response($calendar, Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="oh events.ics"'
        ]);
    }
}
