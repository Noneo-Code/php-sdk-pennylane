<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\JournalReferenceDto;
use PHPUnit\Framework\TestCase;

final class JournalReferenceDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedJournalReferencePayload(): void
    {
        $dto = JournalReferenceDto::fromArray([
            'id' => 123,
            'url' => 'https://app.pennylane.com/api/external/v2/journals/123',
        ]);

        self::assertSame(123, $dto->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/journals/123', $dto->url);
    }
}
