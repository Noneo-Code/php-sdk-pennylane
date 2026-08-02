<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SepaMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutSepaMandate extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $bank = null,
        private readonly ?string $bic = null,
        private readonly ?string $iban = null,
        private readonly ?SepaMandateSequenceType $sequenceType = null,
        private readonly ?string $signedAt = null,
        private readonly ?string $identifier = null,
        private readonly ?int $customerId = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/sepa_mandates/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'bank' => $this->bank,
            'bic' => $this->bic,
            'iban' => $this->iban,
            'sequence_type' => $this->sequenceType,
            'signed_at' => $this->signedAt,
            'identifier' => $this->identifier,
            'customer_id' => $this->customerId,
        ]);
    }
}
