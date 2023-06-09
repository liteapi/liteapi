<?php

use LiteApi\Test\resources\classes\Logger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$projectDir = realpath(__DIR__ . '/../');

return [
    'projectDir' => $projectDir,
    'trustedIPs' => [],
    'extensions' => [
    ],
    'container' => [
        Logger::class => []
    ],
    'services' => [realpath(__DIR__ . '/../../../classes/')],
    'cache' => [
        'class' => FilesystemAdapter::class,
        'args' => [
            'kernel', 0, $projectDir . '/var/cache'
        ]
    ]
];
