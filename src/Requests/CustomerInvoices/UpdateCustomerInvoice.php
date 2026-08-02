<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewTransactionReference;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\Payload;
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
        private readonly string|Clear|null $date = null,
        private readonly string|Clear|null $deadline = null,
        private readonly int|Clear|null $customerId = null,
        private readonly int|Clear|null $customerInvoiceTemplateId = null,
        private readonly string|Clear|null $pdfInvoiceFreeText = null,
        private readonly string|Clear|null $pdfInvoiceSubject = null,
        private readonly string|Clear|null $pdfDescription = null,
        private readonly Currency|Clear|null $currency = null,
        private readonly string|Clear|null $specialMention = null,
        private readonly Discount|Clear|null $discount = null,
        private readonly BillingLanguage|Clear|null $language = null,
        private readonly InvoiceLinesPatch|Clear|null $invoiceLines = null,
        private readonly string|Clear|null $label = null,
        private readonly string|Clear|null $externalReference = null,
        private readonly NewTransactionReference|Clear|null $transactionReference = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return Payload::build([
            'date' => $this->date,
            'deadline' => $this->deadline,
            'customer_id' => $this->customerId,
            'customer_invoice_template_id' => $this->customerInvoiceTemplateId,
            'pdf_invoice_free_text' => $this->pdfInvoiceFreeText,
            'pdf_invoice_subject' => $this->pdfInvoiceSubject,
            'pdf_description' => $this->pdfDescription,
            'currency' => $this->currency,
            'special_mention' => $this->specialMention,
            'discount' => $this->discount,
            'language' => $this->language,
            'invoice_lines' => $this->invoiceLines,
            'label' => $this->label,
            'external_reference' => $this->externalReference,
            'transaction_reference' => $this->transactionReference,
        ]);
    }
}
