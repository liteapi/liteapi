<?php

namespace LiteApi\Http;

class HeadersBag extends ValuesBag
{

    public function add(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

}