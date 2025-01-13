<?php

namespace Nanoyaki\DiscordEventsToIcs;

use Symfony\Component\Dotenv\Dotenv;

require "../vendor/autoload.php";

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$app = new App($_ENV["BOT_TOKEN"]);
$events = $app->getGuildScheduledEvents($_ENV["GUILD_ID"]);
$app->scheduledEventsToIcs($events);