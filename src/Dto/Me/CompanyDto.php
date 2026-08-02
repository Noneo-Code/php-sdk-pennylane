<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

final readonly class CompanyDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $regNo,
        public AccountingLogic $accountingLogic,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $regNo = $body['reg_no'] ?? null;
        $accountingLogic = $body['accounting_logic'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('L\'entreprise de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($name) || !is_string($regNo)) {
            throw new \InvalidArgumentException('L\'entreprise de la réponse Pennylane doit contenir "name" et "reg_no" sous forme de texte.');
        }

        if (!is_string($accountingLogic)) {
            throw new \InvalidArgumentException('L\'entreprise de la réponse Pennylane doit contenir une "accounting_logic" textuelle.');
        }

        return new self($id, $name, $regNo, AccountingLogic::from($accountingLogic));
    }
}
