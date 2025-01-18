<?php

namespace Nanoyaki\DiscordEventsToIcs\Services\Discord;

class Validator
{
    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function exists(string $key, array $object): bool
    {
        return array_key_exists($key, $object);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isNull(string $key, array $object): bool
    {
        return self::exists($key, $object) && is_null($object[$key]);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isInt(string $key, array $object): bool
    {
        return self::exists($key, $object) && is_int($object[$key]);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isNullableInt(string $key, array $object): bool
    {
        return
            self::exists($key, $object)
            && (
                is_int($object[$key])
                || is_null($object[$key])
            );
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isArray(string $key, array $object): bool
    {
        return self::exists($key, $object) && is_array($object[$key]);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isNullableArray(string $key, array $object): bool
    {
        return
            self::exists($key, $object)
            && (
                is_array($object[$key])
                || is_null($object[$key])
            );
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isObject(string $key, array $object): bool
    {
        return self::isArray($key, $object);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isNullableObject(string $key, array $object): bool
    {
        return self::isNullableArray($key, $object);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isString(string $key, array $object): bool
    {
        return self::exists($key, $object) && is_string($object[$key]);
    }

    /**
     * @param string $key
     * @param array<mixed> $object
     * @return bool
     */
    public static function isNullableString(string $key, array $object): bool
    {
        return
            self::exists($key, $object)
            && (
                is_string($object[$key])
                || is_null($object[$key])
            );
    }

    public static function assert(string $description, bool ...$assertions): void
    {
        assert(
            array_all($assertions, fn($assertion) => $assertion),
            "Client API validation: $description"
        );
    }
}