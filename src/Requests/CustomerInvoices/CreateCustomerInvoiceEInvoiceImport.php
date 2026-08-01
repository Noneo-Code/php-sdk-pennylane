<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\OverrideInvoiceLine;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

final class CreateCustomerInvoiceEInvoiceImport extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /**
     * @param resource|string            $fileContents
     * @param ?list<OverrideInvoiceLine> $overrideInvoiceLines
     */
    public function __construct(
        private readonly mixed $fileContents,
        private readonly string $filename,
        private readonly ?bool $sendToPa = null,
        private readonly ?EInvoiceImportOptions $invoiceOptions = null,
        private readonly ?array $overrideInvoiceLines = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/customer_invoices/e_invoices/imports';
    }

    /** @return array<MultipartValue> */
    protected function defaultBody(): array
    {
        $parts = [
            new MultipartValue('file', $this->fileContents, $this->filename),
        ];

        if (null !== $this->sendToPa) {
            $parts[] = new MultipartValue('send_to_pa', $this->sendToPa ? 'true' : 'false');
        }

        if ($this->invoiceOptions instanceof EInvoiceImportOptions) {
            $parts[] = new MultipartValue(
                'invoice_options',
                (string) json_encode($this->invoiceOptions->toArray()),
                null,
                ['Content-Type' => 'application/json'],
            );
        }

        if (null !== $this->overrideInvoiceLines && [] !== $this->overrideInvoiceLines) {
            $parts[] = new MultipartValue(
                'override_invoice_lines',
                (string) json_encode(array_map(
                    static fn (OverrideInvoiceLine $line): array => $line->toArray(),
                    $this->overrideInvoiceLines,
                )),
                null,
                ['Content-Type' => 'application/json'],
            );
        }

        return $parts;
    }
}
