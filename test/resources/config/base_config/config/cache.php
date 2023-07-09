<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$projectDir = realpath(__DIR__ . '/../');

return [
    'class' => FilesystemAdapter::class,
    'args' => [
        'kernel', 0, $projectDir . '/var/cache'
    ]
];
