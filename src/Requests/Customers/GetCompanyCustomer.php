<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Customers;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetCompanyCustomer extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/company_customers/{$this->id}";
    }
}
