<?php

namespace LiteApi\Component\Common;

use Exception;
use LiteApi\Component\Config\Wrapper\Setter;
use LiteApi\Exception\ProgrammerException;
use ReflectionClass;

class ObjectWrapper
{

    /**
     * Method to create objects from array:
     * Array should have key 'class' and optional 'args' and 'setters'
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
        $reflectionClass = new ReflectionClass($config['class']);
        if (isset($config['args'])) {
            $object = $reflectionClass->newInstanceArgs($config['args']);
        } else {
            $object = new $config['class']();
        }
        if (isset($config['setters'])) {
            $setters = $config['setters'];
            if (!is_array($setters)) {
                throw new ProgrammerException();
            }
            foreach ($setters as $setter) {
                try {
                    $setterWrapper = new Setter($setter);
                    $reflectionClass->getMethod($setterWrapper->method)->invoke($object, $setterWrapper->args);
                } catch (Exception $e) {
                    throw new ProgrammerException('Cannot use setter: ' . var_export($setter, true), 0, $e);
                }
            }
        }
        return $object;
    }

}