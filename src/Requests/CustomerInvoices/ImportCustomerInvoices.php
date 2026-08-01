<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewImportedInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class ImportCustomerInvoices extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<NewImportedInvoiceLine> $invoiceLines */
    public function __construct(
        private readonly int $fileAttachmentId,
        private readonly string $date,
        private readonly string $deadline,
        private readonly int $customerId,
        private readonly string $currencyAmountBeforeTax,
        private readonly string $currencyAmount,
        private readonly string $currencyTax,
        private readonly array $invoiceLines,
        private readonly bool $importAsIncomplete = false,
        private readonly ?string $invoiceNumber = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $amount = null,
        private readonly ?string $tax = null,
        private readonly ?string $label = null,
        private readonly ?NewTransactionReference $transactionReference = null,
        private readonly bool $convertToEInvoice = false,
        private readonly ?Discount $discount = null,
        private readonly ?string $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/customer_invoices/import';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'file_attachment_id' => $this->fileAttachmentId,
            'import_as_incomplete' => $this->importAsIncomplete ? true : null,
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
            'label' => $this->label,
            'transaction_reference' => $this->transactionReference?->toArray(),
            'invoice_lines' => array_map(
                static fn (NewImportedInvoiceLine $line): array => $line->toArray(),
                $this->invoiceLines,
            ),
            'convert_to_e_invoice' => $this->convertToEInvoice ? true : null,
            'discount' => $this->discount?->toArray(),
            'external_reference' => $this->externalReference,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
