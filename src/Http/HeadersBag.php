<?php

namespace LiteApi\Http;

class HeadersBag extends ValuesBag
{

    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }
    }

    public function add(string $key, string $value): void
    {
        $key = strtr($key, self::UPPER, self::LOWER);
        $this->values[$key] = $value;
    }

    /**
     * @return array<string,string>
     */
    public function getAllToSend(): array
    {
        $headers = [];
        foreach ($this->values as $key => $value) {
            $key = strtr($key, self::LOWER, self::UPPER);
            $headers[$key] = $value;
        }
        return $headers;
    }

    public function getAll(): array
    {
        return $this->values;
    }

}