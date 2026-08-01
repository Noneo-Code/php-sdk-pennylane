<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

final readonly class ContactDto
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $role,
        public string $email,
        public string $telephoneNumber,
        public string $mobileNumber,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $firstName = $body['first_name'] ?? null;
        $lastName = $body['last_name'] ?? null;
        $role = $body['role'] ?? null;
        $email = $body['email'] ?? null;
        $telephoneNumber = $body['telephone_number'] ?? null;
        $mobileNumber = $body['mobile_number'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($firstName)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "first_name" textuel.');
        }

        if (!is_string($lastName)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "last_name" textuel.');
        }

        if (!is_string($role)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "role" textuel.');
        }

        if (!is_string($email)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "email" textuel.');
        }

        if (!is_string($telephoneNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "telephone_number" textuel.');
        }

        if (!is_string($mobileNumber)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "mobile_number" textuel.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            role: $role,
            email: $email,
            telephoneNumber: $telephoneNumber,
            mobileNumber: $mobileNumber,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
