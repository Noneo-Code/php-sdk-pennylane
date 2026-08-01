<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Quotes;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class SendByEmailQuote extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param ?list<string> $recipients */
    public function __construct(
        private readonly int $id,
        private readonly ?array $recipients = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/quotes/{$this->id}/send_by_email";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'recipients' => $this->recipients,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
