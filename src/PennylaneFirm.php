<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;

final class PennylaneFirm extends Connector
{
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticate($authenticator);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/firm/v1';
    }

    public function company(int $companyId): CompanyScope
    {
        return new CompanyScope($this, $companyId);
    }
}
