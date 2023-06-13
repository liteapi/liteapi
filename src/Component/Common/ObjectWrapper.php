<?php

namespace LiteApi\Component\Common;

use LiteApi\Exception\ProgrammerException;
use ReflectionClass;

class ObjectWrapper
{

    /**
     * Method to create objects from array:
     * Array should have key 'class' and optional 'args'
     *
     * @param array $config
     * @return mixed
     * @throws ProgrammerException
     * @throws \ReflectionException
     */
    public static function parseArrayToObject(array $config): mixed
    {
        if (!isset($config['class'])) {
            throw new ProgrammerException('Cannot create class from config');
        }
        if (isset($config['args'])) {
            return (new ReflectionClass($config['class']))->newInstanceArgs($config['args']);
        } else {
            return new $config['class']();
        }
    }

}