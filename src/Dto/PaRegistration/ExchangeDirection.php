<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PaRegistration;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getPaRegistrations', pointer: 'items.exchange_direction')]
enum ExchangeDirection: string
{
    case Emission = 'emission';
    case Reception = 'reception';
    case EmissionAndReception = 'emission_and_reception';
}
