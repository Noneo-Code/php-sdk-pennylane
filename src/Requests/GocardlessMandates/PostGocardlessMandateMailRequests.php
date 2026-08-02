<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\GocardlessMandates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostGocardlessMandateMailRequests extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<string> $recipients */
    public function __construct(
        private readonly int $customerId,
        private readonly array $recipients,
        private readonly ?string $subject = null,
        private readonly ?string $emailBody = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/gocardless_mandates/mail_requests';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return [
            'customer_id' => $this->customerId,
            'email' => array_filter([
                'subject' => $this->subject,
                'body' => $this->emailBody,
                'recipients' => $this->recipients,
            ], static fn (mixed $value): bool => null !== $value),
        ];
    }
}
