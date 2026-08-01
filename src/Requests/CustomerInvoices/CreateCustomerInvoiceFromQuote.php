<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class CreateCustomerInvoiceFromQuote extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $quoteId,
        private readonly bool $draft,
        private readonly ?string $externalReference = null,
        private readonly ?int $customerInvoiceTemplateId = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/customer_invoices/create_from_quote';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'quote_id' => $this->quoteId,
            'draft' => $this->draft,
            'external_reference' => $this->externalReference,
            'customer_invoice_template_id' => $this->customerInvoiceTemplateId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
