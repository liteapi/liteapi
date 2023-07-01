<?php

namespace LiteApi\Http;

class ValuesBag
{

    /**
     * @var array<string, mixed>
     */
    public array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|int|null
     */
    public function get(string $key, mixed $default = null): string|int|null
    {
        return $this->values[$key] ?? $default;
    }
}