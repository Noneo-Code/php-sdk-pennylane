<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PaRegistration;

enum ExchangeDirection: string
{
    case Emission = 'emission';
    case Reception = 'reception';
    case EmissionAndReception = 'emission_and_reception';
}
