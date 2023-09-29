<?php

namespace LiteApi\Http\ExceptionHandler;

class Parser
{

    /**
     * @param array<int,array<string,string|null>> $config
     */
    public static function parse(array $config): array
    {
        krsort($config, SORT_NUMERIC);
        return $config;
    }



}