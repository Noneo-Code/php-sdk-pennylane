<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateCustomerInvoice extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly ?string $date = null,
        private readonly ?string $deadline = null,
        private readonly ?int $customerId = null,
        private readonly ?int $customerInvoiceTemplateId = null,
        private readonly ?string $pdfInvoiceFreeText = null,
        private readonly ?string $pdfInvoiceSubject = null,
        private readonly ?string $pdfDescription = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $specialMention = null,
        private readonly ?Discount $discount = null,
        private readonly ?BillingLanguage $language = null,
        private readonly ?InvoiceLinesPatch $invoiceLines = null,
        private readonly ?string $label = null,
        private readonly ?string $externalReference = null,
        private readonly ?NewTransactionReference $transactionReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'date' => $this->date,
            'deadline' => $this->deadline,
            'customer_id' => $this->customerId,
            'customer_invoice_template_id' => $this->customerInvoiceTemplateId,
            'pdf_invoice_free_text' => $this->pdfInvoiceFreeText,
            'pdf_invoice_subject' => $this->pdfInvoiceSubject,
            'pdf_description' => $this->pdfDescription,
            'currency' => $this->currency?->value,
            'special_mention' => $this->specialMention,
            'discount' => $this->discount?->toArray(),
            'language' => $this->language?->value,
            'invoice_lines' => $this->invoiceLines?->toArray(),
            'label' => $this->label,
            'external_reference' => $this->externalReference,
            'transaction_reference' => $this->transactionReference?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
