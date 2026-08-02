<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

enum AccountNumbersType: string
{
    case Original = 'original';
    case Pennylane = 'pennylane';
}
