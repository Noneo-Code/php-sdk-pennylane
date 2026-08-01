<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostCustomerInvoices extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param list<NewProductInvoiceLine|NewStandardInvoiceLine> $invoiceLines
     * @param list<NewInvoiceLineSection>                        $invoiceLineSections
     */
    public function __construct(
        private readonly string $date,
        private readonly string $deadline,
        private readonly int $customerId,
        private readonly array $invoiceLines,
        private readonly bool $draft = false,
        private readonly ?int $customerInvoiceTemplateId = null,
        private readonly ?string $pdfInvoiceFreeText = null,
        private readonly ?string $pdfInvoiceSubject = null,
        private readonly ?string $pdfDescription = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $specialMention = null,
        private readonly ?BillingLanguage $language = null,
        private readonly ?Discount $discount = null,
        private readonly array $invoiceLineSections = [],
        private readonly ?string $label = null,
        private readonly ?string $externalReference = null,
        private readonly ?NewTransactionReference $transactionReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/customer_invoices';
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
            'language' => $this->language?->value,
            'discount' => $this->discount?->toArray(),
            'invoice_line_sections' => [] !== $this->invoiceLineSections
                ? array_map(static fn (NewInvoiceLineSection $section): array => $section->toArray(), $this->invoiceLineSections)
                : null,
            'label' => $this->label,
            'external_reference' => $this->externalReference,
            'draft' => $this->draft ? true : null,
            'transaction_reference' => $this->transactionReference?->toArray(),
            'invoice_lines' => array_map(
                static fn (NewProductInvoiceLine|NewStandardInvoiceLine $line): array => $line->toArray(),
                $this->invoiceLines,
            ),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
