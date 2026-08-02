<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Journal;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use PHPUnit\Framework\TestCase;

final class JournalDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedJournalPayload(): void
    {
        $dto = JournalDto::fromArray([
            'code' => 'RB',
            'id' => 42,
            'label' => 'Journal de Reprise Balance',
            'type' => 'miscellaneous',
        ]);

        self::assertSame('RB', $dto->code);
        self::assertSame(42, $dto->id);
        self::assertSame('Journal de Reprise Balance', $dto->label);
        self::assertSame('miscellaneous', $dto->type);
    }
}
