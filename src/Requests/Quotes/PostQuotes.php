<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Quotes;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewInvoiceLineSection;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteStandardInvoiceLine;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostQuotes extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param list<NewQuoteProductInvoiceLine|NewQuoteStandardInvoiceLine> $invoiceLines
     * @param list<NewInvoiceLineSection>                                  $invoiceLineSections
     */
    public function __construct(
        private readonly string $date,
        private readonly string $deadline,
        private readonly int $customerId,
        private readonly array $invoiceLines,
        private readonly ?int $quoteTemplateId = null,
        private readonly ?string $pdfInvoiceFreeText = null,
        private readonly ?string $pdfInvoiceSubject = null,
        private readonly ?string $pdfDescription = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $specialMention = null,
        private readonly ?BillingLanguage $language = null,
        private readonly ?Discount $discount = null,
        private readonly array $invoiceLineSections = [],
        private readonly ?string $externalReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/quotes';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'date' => $this->date,
            'deadline' => $this->deadline,
            'customer_id' => $this->customerId,
            'quote_template_id' => $this->quoteTemplateId,
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
            'external_reference' => $this->externalReference,
            'invoice_lines' => array_map(
                static fn (NewQuoteProductInvoiceLine|NewQuoteStandardInvoiceLine $line): array => $line->toArray(),
                $this->invoiceLines,
            ),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
