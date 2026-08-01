<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Quotes;

use Noneo\Sdk\Pennylane\Dto\Quote\QuoteStatus;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateStatusQuote extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly QuoteStatus $status,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/quotes/{$this->id}/update_status";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return ['status' => $this->status->value];
    }
}
