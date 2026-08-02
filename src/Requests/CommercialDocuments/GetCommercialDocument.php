<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\CommercialDocuments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetCommercialDocument extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/commercial_documents/{$this->id}";
    }
}
