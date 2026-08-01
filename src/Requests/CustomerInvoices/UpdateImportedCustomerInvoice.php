<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImportedInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateImportedCustomerInvoice extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly ?string $date = null,
        private readonly ?string $deadline = null,
        private readonly ?int $customerId = null,
        private readonly ?string $invoiceNumber = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $currencyAmountBeforeTax = null,
        private readonly ?string $currencyAmount = null,
        private readonly ?string $amount = null,
        private readonly ?string $currencyTax = null,
        private readonly ?string $tax = null,
        private readonly ?NewTransactionReference $transactionReference = null,
        private readonly ?ImportedInvoiceLinesPatch $invoiceLines = null,
        private readonly ?string $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}/update_imported";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'date' => $this->date,
            'deadline' => $this->deadline,
            'customer_id' => $this->customerId,
            'invoice_number' => $this->invoiceNumber,
            'currency' => $this->currency?->value,
            'currency_amount_before_tax' => $this->currencyAmountBeforeTax,
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'transaction_reference' => $this->transactionReference?->toArray(),
            'invoice_lines' => $this->invoiceLines?->toArray(),
            'external_reference' => $this->externalReference,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
