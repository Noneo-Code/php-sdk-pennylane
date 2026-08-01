<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CustomerInvoices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

final class PostCustomerInvoiceAppendices extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /** @param resource|string $fileContents */
    public function __construct(
        private readonly int $customerInvoiceId,
        private readonly mixed $fileContents,
        private readonly string $filename,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/customer_invoices/{$this->customerInvoiceId}/appendices";
    }

    /** @return array<MultipartValue> */
    protected function defaultBody(): array
    {
        return [
            new MultipartValue('file', $this->fileContents, $this->filename),
        ];
    }
}
