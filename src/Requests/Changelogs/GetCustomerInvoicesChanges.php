<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Changelogs;

final class GetCustomerInvoicesChanges extends ChangelogRequest
{
    public function resolveEndpoint(): string
    {
        return '/changelogs/customer_invoices';
    }
}
