<?php

namespace LiteApi\Container\Definition;

abstract class DefinedDefinition extends Definition
{

    abstract public function load(): object;

}