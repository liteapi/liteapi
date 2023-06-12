<?php

namespace LiteApi\Event;

enum KernelEvent: string
{

    case AfterBoot = 'KernelAfterBoot';

    case BeforeRequest = 'KernelBeforeRequest';

    case AfterRequest = 'KernelAfterRequest';

    case RequestException = 'RequestException';

    case BeforeCommand = 'KernelBeforeCommand';

    case AfterCommand = 'KernelAfterCommand';

    case OnDestruct = 'KernelOnDestruct';



}
