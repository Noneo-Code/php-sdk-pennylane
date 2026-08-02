<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Changelogs;

final class GetCustomerChanges extends ChangelogRequest
{
    public function resolveEndpoint(): string
    {
        return '/changelogs/customers';
    }
}
