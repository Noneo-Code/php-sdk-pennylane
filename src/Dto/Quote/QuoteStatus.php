<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Quote;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getQuote', pointer: 'status')]
enum QuoteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Denied = 'denied';
    case Invoiced = 'invoiced';
    case Expired = 'expired';
}
