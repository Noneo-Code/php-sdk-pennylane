<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\PaRegistrations;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetPaRegistrations extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/pa_registrations';
    }
}
