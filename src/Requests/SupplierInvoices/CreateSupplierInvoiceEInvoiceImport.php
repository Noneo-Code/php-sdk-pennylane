<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceImportOptions;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

final class CreateSupplierInvoiceEInvoiceImport extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /** @param resource|string $fileContents */
    public function __construct(
        private readonly mixed $fileContents,
        private readonly string $filename,
        private readonly ?EInvoiceImportOptions $invoiceOptions = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/supplier_invoices/e_invoices/imports';
    }

    /** @return array<MultipartValue> */
    protected function defaultBody(): array
    {
        $parts = [
            new MultipartValue('file', $this->fileContents, $this->filename),
        ];

        if ($this->invoiceOptions instanceof EInvoiceImportOptions) {
            $parts[] = new MultipartValue(
                'invoice_options',
                (string) json_encode($this->invoiceOptions->toArray()),
                null,
                ['Content-Type' => 'application/json'],
            );
        }

        return $parts;
    }
}
