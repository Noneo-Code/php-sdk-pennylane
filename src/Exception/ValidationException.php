<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Exception;

/**
 * Entité refusée par les règles métier (422). Le corps documenté ne porte que
 * `{error, status}` : errorCode() et details(), hérités, restent donc vides
 * tant que Pennylane n'enrichit pas la réponse.
 */
final class ValidationException extends PennylaneException
{
}
