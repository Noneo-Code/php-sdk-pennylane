<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Support;

/**
 * Les deux variables d'environnement qui commandent la suite d'intégration, et
 * rien d'autre : un jeton pour la lecture, un second verrou explicite pour
 * l'écriture. La classe ne lit pas `getenv()` elle-même — elle reçoit le
 * tableau — pour que ses règles se testent sans toucher à l'environnement du
 * process.
 */
final class IntegrationEnvironment
{
    /**
     * Préfixe des objets créés par la suite d'écriture. L'API ne permet pas de
     * les supprimer tous ; ce tag est le seul moyen de les retrouver ensuite.
     */
    private const RUN_TAG_PREFIX = 'sdk-test-';

    private static ?string $runTag = null;

    /**
     * @param array<string, string> $variables
     */
    public function __construct(private readonly array $variables = [])
    {
    }

    public static function fromGlobals(): self
    {
        $variables = [];

        foreach (['PENNYLANE_API_TOKEN', 'PENNYLANE_SANDBOX'] as $name) {
            $value = getenv($name);

            if (is_string($value)) {
                $variables[$name] = $value;
            }
        }

        return new self($variables);
    }

    public function isConfigured(): bool
    {
        return null !== $this->trimmed('PENNYLANE_API_TOKEN');
    }

    public function token(): string
    {
        $token = $this->trimmed('PENNYLANE_API_TOKEN');

        if (null === $token) {
            throw new \LogicException('Aucun jeton Pennylane dans l\'environnement.');
        }

        return $token;
    }

    /**
     * Double verrou : le jeton seul ne suffit pas, parce qu'un jeton de
     * production ouvrirait la même API que celui d'un bac à sable.
     */
    public function allowsWrites(): bool
    {
        return $this->isConfigured() && '1' === $this->trimmed('PENNYLANE_SANDBOX');
    }

    /**
     * Motif du `markTestSkipped` pour un test de lecture, ou `null` si la suite
     * peut tourner.
     */
    public function skipReason(): ?string
    {
        return $this->isConfigured()
            ? null
            : 'PENNYLANE_API_TOKEN absent de l\'environnement : suite d\'intégration ignorée.';
    }

    /**
     * Motif du `markTestSkipped` pour un test d'écriture. Le jeton manquant
     * prime : c'est la cause la plus en amont, et l'annoncer évite de faire
     * chercher un `PENNYLANE_SANDBOX` qui ne débloquerait rien.
     */
    public function writeSkipReason(): ?string
    {
        if (!$this->isConfigured()) {
            return $this->skipReason();
        }

        return $this->allowsWrites()
            ? null
            : 'PENNYLANE_SANDBOX=1 absent de l\'environnement : parcours d\'écriture ignoré.';
    }

    /**
     * Identifiant du run courant, stable d'un appel à l'autre : tous les objets
     * créés par une même exécution le portent.
     */
    public static function runTag(): string
    {
        return self::$runTag ??= self::RUN_TAG_PREFIX.bin2hex(random_bytes(6));
    }

    /**
     * Une variable définie mais vide — le cas d'un pipeline sans accès aux
     * variables protégées — vaut une variable absente.
     */
    private function trimmed(string $name): ?string
    {
        $value = trim($this->variables[$name] ?? '');

        return '' === $value ? null : $value;
    }
}
