<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Company;

final readonly class CompanyDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $billingCompanyName,
        public string $siren,
        public string $address,
        public string $city,
        public string $postalCode,
        public string $activityNomenclature,
        public string $activityCode,
        public string $externalId,
        public string $clientCode,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $billingCompanyName = $body['billing_company_name'] ?? null;
        $siren = $body['siren'] ?? null;
        $address = $body['address'] ?? null;
        $city = $body['city'] ?? null;
        $postalCode = $body['postal_code'] ?? null;
        $activityNomenclature = $body['activity_nomenclature'] ?? null;
        $activityCode = $body['activity_code'] ?? null;
        $externalId = $body['external_id'] ?? null;
        $clientCode = $body['client_code'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (
            !is_string($name)
            || !is_string($billingCompanyName)
            || !is_string($siren)
            || !is_string($address)
            || !is_string($city)
            || !is_string($postalCode)
            || !is_string($activityNomenclature)
            || !is_string($activityCode)
            || !is_string($externalId)
            || !is_string($clientCode)
        ) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "name", "billing_company_name", "siren", "address", "city", "postal_code", "activity_nomenclature", "activity_code", "external_id" et "client_code" sous forme de texte.');
        }

        return new self(
            id: $id,
            name: $name,
            billingCompanyName: $billingCompanyName,
            siren: $siren,
            address: $address,
            city: $city,
            postalCode: $postalCode,
            activityNomenclature: $activityNomenclature,
            activityCode: $activityCode,
            externalId: $externalId,
            clientCode: $clientCode,
        );
    }
}
