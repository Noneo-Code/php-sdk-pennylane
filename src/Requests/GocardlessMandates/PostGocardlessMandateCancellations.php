<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\GocardlessMandates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class PostGocardlessMandateCancellations extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly int $gocardlessMandateId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/gocardless_mandates/{$this->gocardlessMandateId}/cancellations";
    }
}
