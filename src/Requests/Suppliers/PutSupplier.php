<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Suppliers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PutSupplier extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /** @param string[]|null $emails */
    public function __construct(
        private readonly int $id,
        private readonly string|Clear|null $name = null,
        private readonly string|Clear|null $establishmentNo = null,
        private readonly string|Clear|null $regNo = null,
        private readonly Address|Clear|null $postalAddress = null,
        private readonly string|Clear|null $vatNumber = null,
        private readonly array|Clear|null $emails = null,
        private readonly string|Clear|null $iban = null,
        private readonly SupplierPaymentMethod|Clear|null $supplierPaymentMethod = null,
        private readonly int|Clear|null $supplierDueDateDelay = null,
        private readonly SupplierDueDateRule|Clear|null $supplierDueDateRule = null,
        private readonly string|Clear|null $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/suppliers/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'name' => $this->name,
            'establishment_no' => $this->establishmentNo,
            'reg_no' => $this->regNo,
            'postal_address' => $this->postalAddress,
            'vat_number' => $this->vatNumber,
            'emails' => $this->emails,
            'iban' => $this->iban,
            'supplier_payment_method' => $this->supplierPaymentMethod,
            'supplier_due_date_delay' => $this->supplierDueDateDelay,
            'supplier_due_date_rule' => $this->supplierDueDateRule,
            'external_reference' => $this->externalReference,
        ]);
    }
}
