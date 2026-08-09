<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\EnumTarget;
use Noneo\Sdk\Pennylane\Tooling\Schema\PayloadTarget;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaLocator;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaReport;
use PHPUnit\Framework\TestCase;

final class SchemaReportTest extends TestCase
{
    public function testAConformingPayloadProducesNoDivergence(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [new PayloadTarget('ProductDtoTest::documentedPayload', new DocumentedBy('getProduct'), ['label' => 'Prestation', 'substance' => 'goods'])],
            [],
        );

        self::assertSame([], $report->divergences());
        self::assertSame(1, $report->checkedCount());
        self::assertTrue($report->isClean());
        self::assertSame(0, $report->exitCode());
        self::assertNull($report->failureSummary());
    }

    public function testItAttributesEachDivergenceToTheFixtureThatCarriesIt(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [new PayloadTarget('ProductDtoTest::documentedPayload', new DocumentedBy('getProduct'), ['label' => 'Prestation', 'inventé' => true])],
            [],
        );

        $divergences = $report->divergences();

        self::assertCount(1, $divergences);
        self::assertSame('ProductDtoTest::documentedPayload', $divergences[0]->subject);
        self::assertSame(['$.inventé : champ absent du schéma.'], $divergences[0]->errors);
        self::assertFalse($report->isClean());
        self::assertSame(1, $report->exitCode());
    }

    public function testItComparesAnnotatedEnumsToTheirSpecValues(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [],
            [new EnumTarget('Substance', new DocumentedBy('getProduct', pointer: 'substance'), ['goods'])],
        );

        self::assertSame(['valeurs de la spec absentes du SDK : services.'], $report->divergences()[0]->errors);
    }

    /**
     * Une annotation qui ne pointe nulle part est un écart comme un autre — la
     * signaler plutôt que d'interrompre le rapport laisse voir tous les autres.
     */
    public function testAnAnnotationPointingAtNothingIsReportedInsteadOfThrowing(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [new PayloadTarget('GhostDtoTest::documentedPayload', new DocumentedBy('getUnicorn'), [])],
            [],
        );

        self::assertSame(['Opération "getUnicorn" absente des specs OpenAPI.'], $report->divergences()[0]->errors);
    }

    /**
     * Le mode bloquant n'a de sens qu'avec une porte de sortie documentée :
     * une divergence connue et assumée sort du verdict, mais reste affichée.
     */
    public function testAnExplicitExclusionLeavesTheVerdictClean(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [new PayloadTarget('ProductDtoTest::documentedPayload', new DocumentedBy('getProduct'), ['inventé' => true])],
            [],
            ['ProductDtoTest::documentedPayload' => 'Champ annoncé par le support, pas encore dans la spec.'],
        );

        self::assertSame([], $report->divergences());
        self::assertTrue($report->isClean());
        self::assertSame(
            [['subject' => 'ProductDtoTest::documentedPayload', 'reason' => 'Champ annoncé par le support, pas encore dans la spec.']],
            $report->excluded(),
        );
    }

    /**
     * Une fixture sans annotation n'est pas vérifiée : c'est un trou, pas une
     * absence de problème. Le rapport la nomme.
     */
    public function testAnUnannotatedPayloadIsReportedAsADivergence(): void
    {
        $report = new SchemaReport(self::locator(), [], [], [], ['OrphanDtoTest::documentedPayload']);

        self::assertSame('OrphanDtoTest::documentedPayload', $report->divergences()[0]->subject);
        self::assertSame(
            ['fixture non rattachée : ajoutez un #[DocumentedBy] ou une exclusion documentée.'],
            $report->divergences()[0]->errors,
        );
    }

    public function testAnUnannotatedPayloadCanBeExcludedLikeAnyOtherTarget(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [],
            [],
            ['OrphanDtoTest::documentedPayload' => 'Enveloppe de webhook : absente de la spec, qui ne décrit que des endpoints.'],
            ['OrphanDtoTest::documentedPayload'],
        );

        self::assertTrue($report->isClean());
        self::assertCount(1, $report->excluded());
    }

    public function testTheFailureSummaryCountsBothKindsOfTarget(): void
    {
        $report = new SchemaReport(
            self::locator(),
            [new PayloadTarget('ProductDtoTest::documentedPayload', new DocumentedBy('getProduct'), ['inventé' => true])],
            [new EnumTarget('Substance', new DocumentedBy('getProduct', pointer: 'substance'), ['goods'])],
        );

        self::assertSame(
            '2 cibles divergent de la spec OpenAPI sur 2 vérifiées.',
            $report->failureSummary(),
        );
    }

    private static function locator(): SchemaLocator
    {
        return new SchemaLocator([['paths' => [
            '/api/external/v2/products/{id}' => [
                'get' => [
                    'operationId' => 'getProduct',
                    'responses' => ['200' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'substance' => ['type' => 'string', 'enum' => ['goods', 'services']],
                        ],
                    ]]]]],
                ],
            ],
        ]]]);
    }
}
