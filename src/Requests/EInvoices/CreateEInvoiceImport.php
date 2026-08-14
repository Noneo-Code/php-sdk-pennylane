<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\EInvoices;

use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

/**
 * Import générique d'une facture électronique, que Pennylane marque déprécié
 * dans sa spec.
 *
 * @deprecated au profit des deux endpoints spécialisés,
 *             `CreateCustomerInvoiceEInvoiceImport` et
 *             `CreateSupplierInvoiceEInvoiceImport`, seuls à accepter les
 *             options d'import
 */
final class CreateEInvoiceImport extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /** @param resource|string $fileContents */
    public function __construct(
        private readonly mixed $fileContents,
        private readonly EInvoiceImportType $type,
        private readonly string $filename,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/e-invoices/imports';
    }

    /** @return array<MultipartValue> */
    protected function defaultBody(): array
    {
        return [
            new MultipartValue('file', $this->fileContents, $this->filename),
            new MultipartValue('type', $this->type->value),
        ];
    }
}
