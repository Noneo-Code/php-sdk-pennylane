<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Me;

final readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public Locale $locale,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $firstName = $body['first_name'] ?? null;
        $lastName = $body['last_name'] ?? null;
        $email = $body['email'] ?? null;
        $locale = $body['locale'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('L\'utilisateur de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($firstName) || !is_string($lastName) || !is_string($email)) {
            throw new \InvalidArgumentException('L\'utilisateur de la réponse Pennylane doit contenir "first_name", "last_name" et "email" sous forme de texte.');
        }

        if (!is_string($locale)) {
            throw new \InvalidArgumentException('L\'utilisateur de la réponse Pennylane doit contenir une "locale" textuelle.');
        }

        return new self($id, $firstName, $lastName, $email, Locale::from($locale));
    }
}
