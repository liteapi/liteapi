<?php

namespace LiteApi\Component\Util;

class PhpArrayExporter
{

    public static function export(array $array): string
    {
        return '<?php' . PHP_EOL . PHP_EOL . var_export($array, true);
    }

}