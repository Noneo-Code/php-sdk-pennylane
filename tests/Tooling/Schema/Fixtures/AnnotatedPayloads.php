<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

/**
 * Cible d'exemple pour `TargetScannerTest` : deux payloads annotés, un
 * troisième que le scanner doit signaler comme non rattaché.
 */
final class AnnotatedPayloads
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getProduct')]
    public static function documentedPayload(): array
    {
        return ['label' => 'Prestation'];
    }

    /** @return array<string, mixed> */
    #[DocumentedBy('postProducts', source: SchemaSource::Request)]
    public static function documentedCreationPayload(): array
    {
        return ['label' => 'Prestation'];
    }

    /** @return array<string, mixed> */
    public static function documentedButUnannotatedPayload(): array
    {
        return ['label' => 'Prestation'];
    }
}
