<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerAccountReferenceDto;
use PHPUnit\Framework\TestCase;

final class LedgerAccountReferenceDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedLedgerAccountReferencePayload(): void
    {
        $dto = LedgerAccountReferenceDto::fromArray([
            'id' => 987,
            'number' => '411000',
            'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42',
        ]);

        self::assertSame(987, $dto->id);
        self::assertSame('411000', $dto->number);
        self::assertSame('https://app.pennylane.com/api/external/v2/ledger_accounts/42', $dto->url);
    }
}
