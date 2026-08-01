<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Quote;

enum QuoteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Denied = 'denied';
    case Invoiced = 'invoiced';
    case Expired = 'expired';
}
