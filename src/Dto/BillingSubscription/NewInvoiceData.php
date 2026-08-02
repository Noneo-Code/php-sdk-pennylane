<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;

final readonly class NewInvoiceData
{
    /**
     * @param list<NewInvoiceLine>        $invoiceLines
     * @param list<NewInvoiceLineSection> $invoiceLineSections
     */
    public function __construct(
        public array $invoiceLines,
        public ?Currency $currency = null,
        public ?int $customerInvoiceTemplateId = null,
        public ?string $pdfInvoiceFreeText = null,
        public ?string $pdfInvoiceSubject = null,
        public ?string $pdfDescription = null,
        public ?string $specialMention = null,
        public ?BillingLanguage $language = null,
        public ?Discount $discount = null,
        public array $invoiceLineSections = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'currency' => $this->currency?->value,
            'customer_invoice_template_id' => $this->customerInvoiceTemplateId,
            'pdf_invoice_free_text' => $this->pdfInvoiceFreeText,
            'pdf_invoice_subject' => $this->pdfInvoiceSubject,
            'pdf_description' => $this->pdfDescription,
            'special_mention' => $this->specialMention,
            'language' => $this->language?->value,
            'discount' => $this->discount?->toArray(),
            'invoice_line_sections' => [] !== $this->invoiceLineSections
                ? array_map(static fn (NewInvoiceLineSection $section): array => $section->toArray(), $this->invoiceLineSections)
                : null,
            'invoice_lines' => array_map(static fn (NewInvoiceLine $line): array => $line->toArray(), $this->invoiceLines),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
