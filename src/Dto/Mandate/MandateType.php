<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Mandate;

enum MandateType: string
{
    case SepaMandate = 'SepaMandate';
    case Mandate = 'Mandate';
}
