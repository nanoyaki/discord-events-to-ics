<?php

namespace Nanoyaki\DiscordEventsToIcs;

require "../vendor/autoload.php";

$app = new App();
$app->getCalendar()->send();