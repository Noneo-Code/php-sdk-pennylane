<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Support;

use PHPUnit\Framework\TestCase;

final class IntegrationEnvironmentTest extends TestCase
{
    public function testItIsConfiguredWhenTheTokenIsPresent(): void
    {
        $environment = new IntegrationEnvironment(['PENNYLANE_API_TOKEN' => 'tok_123']);

        self::assertTrue($environment->isConfigured());
        self::assertSame('tok_123', $environment->token());
        self::assertNull($environment->skipReason());
    }

    public function testItIsNotConfiguredWithoutAToken(): void
    {
        $environment = new IntegrationEnvironment([]);

        self::assertFalse($environment->isConfigured());
        self::assertSame(
            'PENNYLANE_API_TOKEN absent de l\'environnement : suite d\'intégration ignorée.',
            $environment->skipReason(),
        );
    }

    /**
     * Une variable CI définie mais vide est le cas courant d'un pipeline de MR
     * qui n'a pas accès aux variables protégées : elle ne doit pas donner
     * l'illusion d'un environnement configuré.
     */
    public function testItTreatsABlankTokenAsAbsent(): void
    {
        $environment = new IntegrationEnvironment(['PENNYLANE_API_TOKEN' => "  \n"]);

        self::assertFalse($environment->isConfigured());
    }

    public function testItRefusesToReadAnAbsentToken(): void
    {
        $environment = new IntegrationEnvironment([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Aucun jeton Pennylane dans l\'environnement.');

        $environment->token();
    }

    public function testItAllowsWritesOnlyWithBothTheTokenAndTheSandboxFlag(): void
    {
        $environment = new IntegrationEnvironment([
            'PENNYLANE_API_TOKEN' => 'tok_123',
            'PENNYLANE_SANDBOX' => '1',
        ]);

        self::assertTrue($environment->allowsWrites());
        self::assertNull($environment->writeSkipReason());
    }

    public function testItRefusesWritesWithoutTheSandboxFlag(): void
    {
        $environment = new IntegrationEnvironment(['PENNYLANE_API_TOKEN' => 'tok_123']);

        self::assertFalse($environment->allowsWrites());
        self::assertSame(
            'PENNYLANE_SANDBOX=1 absent de l\'environnement : parcours d\'écriture ignoré.',
            $environment->writeSkipReason(),
        );
    }

    /**
     * Le second verrou est un garde-fou contre l'écriture accidentelle dans une
     * comptabilité de production : seul `1` l'ouvre, pour qu'un `true` ou un
     * `yes` recopié d'ailleurs échoue côté sûr.
     */
    public function testItRefusesWritesForAnySandboxValueOtherThanOne(): void
    {
        $environment = new IntegrationEnvironment([
            'PENNYLANE_API_TOKEN' => 'tok_123',
            'PENNYLANE_SANDBOX' => 'true',
        ]);

        self::assertFalse($environment->allowsWrites());
    }

    public function testItReportsTheMissingTokenFirstWhenOnlyTheSandboxFlagIsSet(): void
    {
        $environment = new IntegrationEnvironment(['PENNYLANE_SANDBOX' => '1']);

        self::assertFalse($environment->allowsWrites());
        self::assertSame($environment->skipReason(), $environment->writeSkipReason());
    }

    /**
     * Les objets créés par la suite d'écriture ne sont pas supprimables via
     * l'API : ils se retrouvent par ce tag, qui doit donc rester le même
     * pendant tout le run.
     */
    public function testTheRunTagIsStableAcrossCallsAndCarriesTheSdkPrefix(): void
    {
        $tag = IntegrationEnvironment::runTag();

        self::assertSame($tag, IntegrationEnvironment::runTag());
        self::assertStringStartsWith('sdk-test-', $tag);
        self::assertGreaterThan(strlen('sdk-test-'), strlen($tag));
    }
}
