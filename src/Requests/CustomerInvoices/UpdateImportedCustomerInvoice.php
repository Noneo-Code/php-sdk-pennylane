<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImportedInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
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
        private readonly string|Clear|null $date = null,
        private readonly string|Clear|null $deadline = null,
        private readonly int|Clear|null $customerId = null,
        private readonly string|Clear|null $invoiceNumber = null,
        private readonly Currency|Clear|null $currency = null,
        private readonly string|Clear|null $currencyAmountBeforeTax = null,
        private readonly string|Clear|null $currencyAmount = null,
        private readonly string|Clear|null $amount = null,
        private readonly string|Clear|null $currencyTax = null,
        private readonly string|Clear|null $tax = null,
        private readonly NewTransactionReference|Clear|null $transactionReference = null,
        private readonly ImportedInvoiceLinesPatch|Clear|null $invoiceLines = null,
        private readonly string|Clear|null $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}/update_imported";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'date' => $this->date,
            'deadline' => $this->deadline,
            'customer_id' => $this->customerId,
            'invoice_number' => $this->invoiceNumber,
            'currency' => $this->currency,
            'currency_amount_before_tax' => $this->currencyAmountBeforeTax,
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'transaction_reference' => $this->transactionReference,
            'invoice_lines' => $this->invoiceLines,
            'external_reference' => $this->externalReference,
        ]);
    }
}
