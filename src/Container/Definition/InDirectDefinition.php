<?php

namespace LiteApi\Container\Definition;

class InDirectDefinition extends Definition
{
    public string $serviceName;

    /**
     * @param string $serviceName
     */
    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }
}