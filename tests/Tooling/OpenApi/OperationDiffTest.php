<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\Coverage\SpecOperation;
use Noneo\Sdk\Pennylane\Tooling\OpenApi\OperationDiff;
use PHPUnit\Framework\TestCase;

final class OperationDiffTest extends TestCase
{
    public function testReportsNoDriftWhenBothSidesHoldTheSameOperations(): void
    {
        $committed = [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')];
        $fresh = [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')];

        $diff = OperationDiff::between($committed, $fresh);

        self::assertFalse($diff->hasDrift());
        self::assertSame([], $diff->added);
        self::assertSame([], $diff->removed);
        self::assertSame([], $diff->changed);
    }

    /**
     * L'ordre des `paths` d'un fichier régénéré n'est pas stable : un diff qui le
     * prendrait pour une dérive crierait au loup à chaque veille.
     */
    public function testIgnoresTheOrderOfOperations(): void
    {
        $committed = [
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
            new SpecOperation('GET', '/api/external/v2/suppliers', 'getSuppliers'),
        ];
        $fresh = [
            new SpecOperation('GET', '/api/external/v2/suppliers', 'getSuppliers'),
            new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
        ];

        self::assertFalse(OperationDiff::between($committed, $fresh)->hasDrift());
    }

    public function testListsOperationsAddedByTheFreshSpec(): void
    {
        $diff = OperationDiff::between(
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
            [
                new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
                new SpecOperation('POST', '/api/external/v2/journals', 'postJournals'),
            ],
        );

        self::assertTrue($diff->hasDrift());
        self::assertCount(1, $diff->added);
        self::assertSame('postJournals', $diff->added[0]->operationId);
    }

    public function testListsOperationsDroppedByTheFreshSpec(): void
    {
        $diff = OperationDiff::between(
            [
                new SpecOperation('GET', '/api/external/v2/journals', 'getJournals'),
                new SpecOperation('POST', '/api/external/v2/journals', 'postJournals'),
            ],
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
        );

        self::assertTrue($diff->hasDrift());
        self::assertCount(1, $diff->removed);
        self::assertSame('postJournals', $diff->removed[0]->operationId);
    }

    public function testListsOperationsWhoseMethodOrPathMoved(): void
    {
        $diff = OperationDiff::between(
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
            [new SpecOperation('GET', '/api/external/v2/ledger_journals', 'getJournals')],
        );

        self::assertTrue($diff->hasDrift());
        self::assertSame([], $diff->added);
        self::assertSame([], $diff->removed);
        self::assertCount(1, $diff->changed);
        self::assertSame('/api/external/v2/journals', $diff->changed[0]['before']->path);
        self::assertSame('/api/external/v2/ledger_journals', $diff->changed[0]['after']->path);
    }

    public function testRendersDriftAsMarkdownWithOnlyTheNonEmptySections(): void
    {
        $diff = OperationDiff::between(
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
            [new SpecOperation('POST', '/api/external/v2/imports', 'createImport')],
        );

        $markdown = $diff->toMarkdown();

        self::assertStringContainsString("### Ajoutées (1)\n\n- `POST /api/external/v2/imports` (`createImport`)", $markdown);
        self::assertStringContainsString("### Supprimées (1)\n\n- `GET /api/external/v2/journals` (`getJournals`)", $markdown);
        self::assertStringNotContainsString('Modifiées', $markdown);
    }

    public function testRendersTheChangedSectionAsABeforeAfterLine(): void
    {
        $diff = OperationDiff::between(
            [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')],
            [new SpecOperation('POST', '/api/external/v2/journals', 'getJournals')],
        );

        self::assertStringContainsString(
            "### Modifiées (1)\n\n- `getJournals` : `GET /api/external/v2/journals` → `POST /api/external/v2/journals`",
            $diff->toMarkdown(),
        );
    }

    public function testRendersAnExplicitLineWhenThereIsNoDrift(): void
    {
        $operations = [new SpecOperation('GET', '/api/external/v2/journals', 'getJournals')];

        self::assertSame(
            'Aucune dérive : les opérations committées correspondent à la spec publiée.',
            OperationDiff::between($operations, $operations)->toMarkdown(),
        );
    }
}
