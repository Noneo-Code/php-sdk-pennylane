<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\TrialBalance;

use Noneo\Sdk\Pennylane\Dto\TrialBalance\TrialBalanceLineDto;
use PHPUnit\Framework\TestCase;

final class TrialBalanceLineDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedTrialBalanceLinePayload(): void
    {
        $dto = TrialBalanceLineDto::fromArray([
            'number' => '512',
            'formatted_number' => '51200000',
            'label' => 'Capital souscrit - appelé, versé',
            'debits' => '45353.95',
            'credits' => '2343.05',
        ]);

        self::assertSame('512', $dto->number);
        self::assertSame('51200000', $dto->formattedNumber);
        self::assertSame('Capital souscrit - appelé, versé', $dto->label);
        self::assertSame('45353.95', $dto->debits);
        self::assertSame('2343.05', $dto->credits);
    }
}
