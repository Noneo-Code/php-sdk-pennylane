<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Suppliers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostSupplier extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param string[]|null $emails */
    public function __construct(
        private readonly string $name,
        private readonly ?string $establishmentNo = null,
        private readonly ?string $regNo = null,
        private readonly ?Address $postalAddress = null,
        private readonly ?string $vatNumber = null,
        private readonly ?string $ledgerAccountNumber = null,
        private readonly ?array $emails = null,
        private readonly ?string $iban = null,
        private readonly ?SupplierPaymentMethod $supplierPaymentMethod = null,
        private readonly ?int $supplierDueDateDelay = null,
        private readonly ?SupplierDueDateRule $supplierDueDateRule = null,
        private readonly ?string $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/suppliers';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'name' => $this->name,
            'establishment_no' => $this->establishmentNo,
            'reg_no' => $this->regNo,
            'postal_address' => $this->postalAddress?->toArray(),
            'vat_number' => $this->vatNumber,
            'ledger_account' => null !== $this->ledgerAccountNumber ? ['number' => $this->ledgerAccountNumber] : null,
            'emails' => $this->emails,
            'iban' => $this->iban,
            'supplier_payment_method' => $this->supplierPaymentMethod?->value,
            'supplier_due_date_delay' => $this->supplierDueDateDelay,
            'supplier_due_date_rule' => $this->supplierDueDateRule?->value,
            'external_reference' => $this->externalReference,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
