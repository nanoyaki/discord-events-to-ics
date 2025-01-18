<?php

namespace Nanoyaki\DiscordEventsToIcs\Entities\Discord;

use Nanoyaki\DiscordEventsToIcs\Services\Discord\Validator;

readonly class User
{
    public string $id;

    public string $username;

    public string $discriminator;

    public ?string $globalName;

    /**
     * We don't need more information than specified in this
     * class. Extend if necessary
     *
     * @param array<mixed> $apiUser
     */
    public function __construct(array $apiUser)
    {
        Validator::assert(
            "User",
            Validator::isString("id", $apiUser),
            Validator::isString("username", $apiUser),
            Validator::isString("discriminator", $apiUser),
            Validator::isNullableString("global_name", $apiUser)
        );

        $this->id = $apiUser["id"];
        $this->username = $apiUser["username"];
        $this->discriminator = $apiUser["discriminator"];
        $this->globalName = $apiUser["global_name"];
    }
}