<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\ProAccountMandates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostProAccountMandateMailRequests extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $customerId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/pro_account/mandate_requests';
    }

    /** @return array{customer_id: int} */
    protected function defaultBody(): array
    {
        return ['customer_id' => $this->customerId];
    }
}
