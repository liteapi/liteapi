<?php

namespace LiteApi\Component\Config\Wrapper;

use LiteApi\Component\Common\ArrayWrapper;
use LiteApi\Exception\ProgrammerException;

class EnvWrapper extends ArrayWrapper
{

    public string $env;
    public bool $debug;
    public array $params;

    protected function wrap(array $config): void
    {
        $envName = 'APP_ENV';
        $debugName = 'APP_DEBUG';
        //$secretKey = 'APP_SECRET_KEY';

        $this->assertHasKeys($config, [$envName, $debugName]);
        if (!in_array($config[$envName], ['dev', 'prod', 'test'])) {
            throw new ProgrammerException('ENV must be one of this value: \'dev, prod, test\'');
        }
        $this->env = $config[$envName];
        $this->debug = filter_var($config[$debugName], FILTER_VALIDATE_BOOL);

        $config[$debugName] = $this->debug;
        $this->params = $config;
    }
}