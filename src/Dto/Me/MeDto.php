<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

final readonly class MeDto
{
    /** @param list<string> $scopes */
    public function __construct(
        public ?UserDto $user,
        public CompanyDto $company,
        public array $scopes,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $user = $body['user'] ?? null;
        $company = $body['company'] ?? null;
        $scopes = $body['scopes'] ?? null;

        if (null !== $user && !is_array($user)) {
            throw new \InvalidArgumentException('L\'"user" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($company)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "company".');
        }

        if (!is_array($scopes)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "scopes".');
        }

        return new self(
            user: is_array($user) ? UserDto::fromArray($user) : null,
            company: CompanyDto::fromArray($company),
            scopes: array_values(array_filter($scopes, static fn (mixed $scope): bool => is_string($scope))),
        );
    }
}
