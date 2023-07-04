<?php

namespace LiteApi\Route;

use DateTime;
use DateTimeInterface;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\ResponseStatus;

enum QueryType
{

    case String;
    case Int;
    case Float;
    case DateTime;
    case Date;

    /**
     * @param string $value
     * @return mixed
     * @throws HttpException
     */
    public function convertValue(string $value): mixed
    {
        switch ($this) {
            case self::String:
                break;
            case self::Int:
                $newValue = filter_var($value, FILTER_VALIDATE_INT);
                if ($newValue === false) {
                    throw new HttpException(ResponseStatus::BadRequest, $value . ' must be valid integer');
                }
                $value = $newValue;
                break;
            case self::Float:
                $newValue = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($newValue === false) {
                    throw new HttpException(ResponseStatus::BadRequest, $value . ' must be valid float');
                }
                $value = $newValue;
                break;
            case self::DateTime:
                $originalValue = $value;
                $value = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $value);
                if ($value === false) {
                    throw new HttpException(ResponseStatus::BadRequest, $originalValue . 'must be in format Y-m-d');
                }
                break;
            case self::Date:
                $originalValue = $value;
                $value = DateTime::createFromFormat('Y-m-d', $value);
                if ($value === false) {
                    throw new HttpException(ResponseStatus::BadRequest, $originalValue . 'must be in format Y-m-d');
                }
                break;
        }
        return $value;
    }

}
