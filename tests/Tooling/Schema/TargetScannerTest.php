<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures\AnnotatedPayloads;
use Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures\AnnotatedSubstance;
use Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures\NonStaticPayload;
use Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures\UnannotatedPayloads;
use Noneo\Sdk\Pennylane\Tests\Tooling\Schema\Fixtures\UnannotatedSubstance;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;
use Noneo\Sdk\Pennylane\Tooling\Schema\TargetScanner;
use PHPUnit\Framework\TestCase;

final class TargetScannerTest extends TestCase
{
    public function testItCollectsEveryAnnotatedPayloadMethodAndCallsIt(): void
    {
        $targets = TargetScanner::payloadsIn([AnnotatedPayloads::class, UnannotatedPayloads::class]);

        self::assertCount(2, $targets);
        self::assertSame(AnnotatedPayloads::class.'::documentedPayload', $targets[0]->subject);
        self::assertSame('getProduct', $targets[0]->documentedBy->operationId);
        self::assertSame(SchemaSource::Response, $targets[0]->documentedBy->source);
        self::assertSame(['label' => 'Prestation'], $targets[0]->payload);
    }

    public function testItKeepsTheDeclaredSchemaSourceOfEachPayload(): void
    {
        $targets = TargetScanner::payloadsIn([AnnotatedPayloads::class]);

        self::assertSame(SchemaSource::Request, $targets[1]->documentedBy->source);
    }

    public function testItCollectsAnnotatedEnumsWithTheirBackingValues(): void
    {
        $targets = TargetScanner::enumsIn([AnnotatedSubstance::class, UnannotatedSubstance::class]);

        self::assertCount(1, $targets);
        self::assertSame(AnnotatedSubstance::class, $targets[0]->subject);
        self::assertSame('substance', $targets[0]->documentedBy->pointer);
        self::assertSame(['goods', 'services'], $targets[0]->values);
    }

    /**
     * Une fixture qu'on oublie d'annoter sortirait du contrôle sans que rien
     * ne le dise : le scanner sait aussi nommer ce qu'il n'a pas ramassé.
     */
    public function testItNamesPayloadMethodsThatCarryNoAnnotation(): void
    {
        $orphans = TargetScanner::unannotatedPayloadsIn([AnnotatedPayloads::class, UnannotatedPayloads::class]);

        self::assertSame(
            [
                AnnotatedPayloads::class.'::documentedButUnannotatedPayload',
                UnannotatedPayloads::class.'::documentedPayload',
            ],
            $orphans,
        );
    }

    /**
     * `enumsIn()` reçoit tout ce que le dossier `src/Dto` contient — DTO
     * compris. Ce qui n'est pas un enum n'est pas une cible, pas une erreur.
     */
    public function testItIgnoresClassesThatAreNotEnums(): void
    {
        self::assertSame([], TargetScanner::enumsIn([AnnotatedPayloads::class]));
    }

    /**
     * Le scan par classe ne sert à rien s'il faut lister les classes à la main :
     * la découverte suit la convention PSR-4 du dépôt.
     */
    public function testItDiscoversClassesUnderADirectoryFollowingPsr4(): void
    {
        $classes = TargetScanner::classesIn(__DIR__.'/Fixtures', __NAMESPACE__.'\\Fixtures');

        self::assertContains(AnnotatedPayloads::class, $classes);
        self::assertContains(AnnotatedSubstance::class, $classes);
    }

    /**
     * Une fixture annotée doit être appelable sans instance : le scanner
     * refuse de deviner comment en construire une.
     */
    public function testItRejectsAnAnnotatedMethodThatIsNotStatic(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(NonStaticPayload::class.'::documentedPayload : une fixture annotée doit être publique et statique.');

        TargetScanner::payloadsIn([NonStaticPayload::class]);
    }
}
