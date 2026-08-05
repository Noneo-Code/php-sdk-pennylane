<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Le verdict : chaque fixture et chaque enum annoté, confronté au schéma dont
 * il se réclame.
 *
 * Une annotation qui ne mène nulle part — opération disparue, chemin
 * introuvable — est traitée comme une divergence et non comme une erreur
 * fatale : le rapport doit montrer *tous* les écarts d'un coup, pas s'arrêter
 * au premier. Après une mise à jour de spec, c'est la différence entre une
 * correction et une dizaine d'allers-retours.
 */
final class SchemaReport
{
    /**
     * @param list<PayloadTarget>   $payloads
     * @param list<EnumTarget>      $enums
     * @param array<string, string> $exclusions sujet => raison de l'écart assumé
     * @param list<string>          $orphans    fixtures repérées sans annotation
     */
    public function __construct(
        private readonly SchemaLocator $locator,
        private readonly array $payloads,
        private readonly array $enums,
        private readonly array $exclusions = [],
        private readonly array $orphans = [],
    ) {
    }

    /**
     * @return list<Divergence>
     */
    public function divergences(): array
    {
        $divergences = [];

        foreach ($this->payloads as $target) {
            if (array_key_exists($target->subject, $this->exclusions)) {
                continue;
            }

            if ([] !== $errors = $this->checkPayload($target)) {
                $divergences[] = new Divergence($target->subject, $errors);
            }
        }

        foreach ($this->enums as $target) {
            if (array_key_exists($target->subject, $this->exclusions)) {
                continue;
            }

            if ([] !== $errors = $this->checkEnum($target)) {
                $divergences[] = new Divergence($target->subject, $errors);
            }
        }

        foreach ($this->orphans as $subject) {
            if (!array_key_exists($subject, $this->exclusions)) {
                $divergences[] = new Divergence($subject, ['fixture non rattachée : ajoutez un #[DocumentedBy] ou une exclusion documentée.']);
            }
        }

        return $divergences;
    }

    public function checkedCount(): int
    {
        return count($this->payloads) + count($this->enums) + count($this->orphans) - count($this->excluded());
    }

    /**
     * Les exclusions effectivement rencontrées. Une exclusion qui ne correspond
     * à aucune cible n'apparaît pas : elle ne dispense de rien.
     *
     * @return list<array{subject: string, reason: string}>
     */
    public function excluded(): array
    {
        $excluded = [];

        $subjects = [
            ...array_map(static fn (PayloadTarget $t): string => $t->subject, $this->payloads),
            ...array_map(static fn (EnumTarget $t): string => $t->subject, $this->enums),
            ...$this->orphans,
        ];

        foreach ($subjects as $subject) {
            if (array_key_exists($subject, $this->exclusions)) {
                $excluded[] = ['subject' => $subject, 'reason' => $this->exclusions[$subject]];
            }
        }

        return $excluded;
    }

    public function isClean(): bool
    {
        return [] === $this->divergences();
    }

    public function exitCode(): int
    {
        return $this->isClean() ? 0 : 1;
    }

    /**
     * Une ligne pour STDERR : le log de CI doit se lire sans le dérouler.
     */
    public function failureSummary(): ?string
    {
        $divergences = $this->divergences();

        if ([] === $divergences) {
            return null;
        }

        return sprintf(
            '%d cible%s divergent de la spec OpenAPI sur %d vérifiée%s.',
            count($divergences),
            1 === count($divergences) ? '' : 's',
            $this->checkedCount(),
            1 === $this->checkedCount() ? '' : 's',
        );
    }

    /**
     * @return list<string>
     */
    private function checkPayload(PayloadTarget $target): array
    {
        try {
            $schema = $this->locate($target->documentedBy);
        } catch (\RuntimeException $exception) {
            return [$exception->getMessage()];
        }

        return SchemaValidator::validate($target->payload, $schema);
    }

    /**
     * @return list<string>
     */
    private function checkEnum(EnumTarget $target): array
    {
        try {
            return EnumCoverage::compare($target->values, $this->locate($target->documentedBy));
        } catch (\RuntimeException $exception) {
            return [$exception->getMessage()];
        }
    }

    /**
     * @return mixed[]
     */
    private function locate(DocumentedBy $documentedBy): array
    {
        return $this->locator->locate($documentedBy->operationId, $documentedBy->source, $documentedBy->pointer);
    }
}
