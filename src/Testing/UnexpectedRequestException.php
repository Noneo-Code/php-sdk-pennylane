<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Testing;

/**
 * Une requête est partie sans qu'aucune réponse simulée ne lui corresponde :
 * le test ne décrit pas tout ce que le code sous test envoie.
 */
final class UnexpectedRequestException extends \RuntimeException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct(sprintf(
            'Requête %s %s inattendue : aucune réponse simulée ne correspond.',
            $method,
            $path,
        ));
    }
}
