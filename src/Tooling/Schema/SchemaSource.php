<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * De quel côté de l'échange vient le schéma auquel une fixture se rattache :
 * ce que l'API rend, ou ce qu'elle accepte.
 */
enum SchemaSource: string
{
    case Response = 'response';
    case Request = 'request';
}
