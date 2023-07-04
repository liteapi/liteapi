<?php

namespace LiteApi\Exception;

class Trigger
{

    public static function warn(string $message): void
    {
        @trigger_error($message, E_USER_WARNING);
    }

    public static function notice(string $message): void
    {
        @trigger_error($message);
    }

    public static function error(string $message): void
    {
        @trigger_error($message, E_USER_ERROR);
    }

    public static function deprecated(string $message): void
    {
        @trigger_error($message, E_USER_DEPRECATED);
    }

}