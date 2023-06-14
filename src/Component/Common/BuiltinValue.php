<?php

namespace LiteApi\Component\Common;

enum BuiltinValue: string
{

    case Int = 'int';
    case String = 'string';
    case Float = 'float';
    case Bool = 'bool';

    public function convertValue(string $value): mixed
    {
        return match ($this) {
            self::Int => filter_var($value, FILTER_VALIDATE_INT),
            self::String => $value,
            self::Bool => filter_var($value, FILTER_VALIDATE_BOOL),
            self::Float => filter_var($value, FILTER_VALIDATE_FLOAT)
        };
    }

}