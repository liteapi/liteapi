<?php

namespace LiteApi\Test\resources\classes;

enum Level: int
{

    case Debug = 100;

    case Info = 200;

    case Notice = 250;

    case Warning = 300;

    case Error = 400;

    case Critical = 500;

    case Alert = 550;

    case Emergency = 600;

    /**
     * @param Level $level Minimal log level
     * @return bool
     */
    public function isToLog(Level $level): bool
    {
        return $this->value >= $level->value;
    }

}