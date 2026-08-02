<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostSepaMandates extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $bic,
        private readonly string $iban,
        private readonly string $signedAt,
        private readonly string $identifier,
        private readonly int $customerId,
        private readonly ?string $bank = null,
        private readonly ?SepaMandateSequenceType $sequenceType = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/sepa_mandates';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'bank' => $this->bank,
            'bic' => $this->bic,
            'iban' => $this->iban,
            'sequence_type' => $this->sequenceType?->value,
            'signed_at' => $this->signedAt,
            'identifier' => $this->identifier,
            'customer_id' => $this->customerId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
