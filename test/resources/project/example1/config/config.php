<?php

use LiteApi\Test\resources\classes\Logger;

$projectDir = realpath(__DIR__ . '/../');

return [
    'projectDir' => $projectDir,
    'trustedIPs' => [],
    'extensions' => [
    ],
    'container' => [
        Logger::class => []
    ],
    'services' => [realpath(__DIR__ . '/../../../classes/')]
];
