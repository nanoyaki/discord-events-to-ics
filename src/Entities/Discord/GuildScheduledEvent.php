<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use DateTimeImmutable;
use Nanoyaki\DiscordEventsToIcs\Enums\Discord\EventStatus;
use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class GuildScheduledEvent
{
    public string $id;

    public string $guildId;

    /**
     * @var string $creatorId we do not care about
     *  events created before October 25th, 2021
     */
    public string $creatorId;

    public string $name;

    public ?string $description;

    public DateTimeImmutable $scheduledStartTime;

    public DateTimeImmutable $scheduledEndTime;

    public EventStatus $status;

    public ?string $entityId;

    /**
     * @var User $creator we do not care about
     *  events created before October 25th, 2021
     */
    public User $creator;

    public int $interestedMembers;

    public ?string $imageHash;

    public ?RecurrenceRule $recurrenceRule;

    /**
     * @param array<mixed> $apiEvent
     */
    public function __construct(array $apiEvent)
    {
        Validator::assert(
            "Guild scheduled event",
            Validator::isString("id", $apiEvent),
            Validator::isString("guild_id", $apiEvent),
            Validator::isString("creator_id", $apiEvent),
            Validator::isString("name", $apiEvent),
            (
                !Validator::exists("description", $apiEvent)
                || Validator::isNullableString("description", $apiEvent)
            ),
            Validator::isString("scheduled_start_time", $apiEvent),
            Validator::isNullableString("scheduled_end_time", $apiEvent),
            Validator::isInt("status", $apiEvent),
            Validator::isNullableString("entity_id", $apiEvent),
            Validator::isObject("creator", $apiEvent),
            Validator::isInt("user_count", $apiEvent),
            (
                !Validator::exists("image", $apiEvent)
                || Validator::isString("image", $apiEvent)
            ),
            Validator::isNullableObject("recurrence_rule", $apiEvent),
        );

        $this->id = $apiEvent["id"];
        $this->guildId = $apiEvent["guild_id"];
        $this->creatorId = $apiEvent["creator_id"];
        $this->name = $apiEvent["name"];
        $this->description = Validator::exists("description", $apiEvent) ? $apiEvent["description"] : null;
        $this->scheduledStartTime = DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $apiEvent["scheduled_start_time"],
            new \DateTimeZone("UTC")
        );
        assert($this->scheduledStartTime instanceof DateTimeImmutable);
        $this->scheduledEndTime = !is_null($apiEvent["scheduled_end_time"])
            ? DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                $apiEvent["scheduled_end_time"],
                new \DateTimeZone("UTC")
            )
            : $this->scheduledStartTime;
        assert($this->scheduledStartTime instanceof DateTimeImmutable);
        $this->status = EventStatus::from($apiEvent["status"]);
        $this->entityId = $apiEvent["entity_id"];
        $this->creator = new User($apiEvent["creator"]);
        $this->interestedMembers = $apiEvent["user_count"];
        $this->imageHash = Validator::exists("image", $apiEvent) ? $apiEvent["image"] : null;
        $this->recurrenceRule = !is_null($apiEvent["recurrence_rule"])
            ? new RecurrenceRule($apiEvent["recurrence_rule"])
            : null;
    }
}