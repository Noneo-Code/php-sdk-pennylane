<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tooling\Schema\EnumCoverage;
use PHPUnit\Framework\TestCase;

final class EnumCoverageTest extends TestCase
{
    public function testItAcceptsAnEnumThatCoversExactlyTheSpecValues(): void
    {
        $schema = ['type' => 'string', 'enum' => ['draft', 'paid']];

        self::assertSame([], EnumCoverage::compare(['draft', 'paid'], $schema));
    }

    public function testTheOrderOfTheCasesDoesNotMatter(): void
    {
        $schema = ['type' => 'string', 'enum' => ['draft', 'paid']];

        self::assertSame([], EnumCoverage::compare(['paid', 'draft'], $schema));
    }

    /**
     * Le cas qui casse en production : l'API rend une valeur que le `from()` du
     * SDK ne connaît pas, et le DTO lève sur une réponse pourtant valide.
     */
    public function testItReportsASpecValueTheSdkDoesNotCover(): void
    {
        $schema = ['type' => 'string', 'enum' => ['draft', 'paid', 'late']];

        self::assertSame(
            ['valeurs de la spec absentes du SDK : late.'],
            EnumCoverage::compare(['draft', 'paid'], $schema),
        );
    }

    /**
     * L'inverse est un écart aussi : un cas que la spec ne connaît plus est
     * soit un reliquat, soit une valeur inventée.
     */
    public function testItReportsASdkCaseTheSpecDoesNotDeclare(): void
    {
        $schema = ['type' => 'string', 'enum' => ['draft', 'paid']];

        self::assertSame(
            ['valeurs du SDK absentes de la spec : proforma.'],
            EnumCoverage::compare(['draft', 'paid', 'proforma'], $schema),
        );
    }

    public function testItReportsBothDirectionsAtOnce(): void
    {
        $schema = ['type' => 'string', 'enum' => ['draft', 'late']];

        self::assertSame(
            [
                'valeurs de la spec absentes du SDK : late.',
                'valeurs du SDK absentes de la spec : paid.',
            ],
            EnumCoverage::compare(['draft', 'paid'], $schema),
        );
    }

    /**
     * La spec accroche souvent une description à une énumération via `allOf` :
     * les valeurs sont alors dans la première branche.
     */
    public function testItFindsTheEnumInsideAnAllOf(): void
    {
        $schema = ['allOf' => [
            ['type' => 'string', 'enum' => ['goods', 'services']],
            ['description' => 'Résout le compte comptable de la ligne.'],
        ]];

        self::assertSame([], EnumCoverage::compare(['goods', 'services'], $schema));
    }

    public function testItFindsTheEnumBehindANullableWrapper(): void
    {
        $schema = ['anyOf' => [
            ['type' => 'string', 'enum' => ['fr', 'en']],
            ['type' => 'null'],
        ]];

        self::assertSame([], EnumCoverage::compare(['fr', 'en'], $schema));
    }

    public function testItRejectsASchemaThatDeclaresNoEnumAtAll(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Le schéma visé ne déclare aucune énumération.');

        EnumCoverage::compare(['draft'], ['type' => 'string']);
    }
}
