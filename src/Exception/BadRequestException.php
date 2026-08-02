<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

/**
 * Requête rejetée avant traitement : filtre mal formé, paramètre hors bornes,
 * corps non conforme au schéma. Le champ `code` du corps, quand il est présent,
 * est exposé par errorCode().
 */
final class BadRequestException extends PennylaneException
{
}
