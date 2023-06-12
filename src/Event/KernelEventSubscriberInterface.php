<?php

namespace LiteApi\Event;

interface KernelEventSubscriberInterface
{

    public static function getEventsDefinitions(): array;

}