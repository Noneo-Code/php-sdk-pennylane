<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\FiscalYears;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostFiscalYears extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $start,
        private readonly string $finish,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/fiscal_years';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return [
            'start' => $this->start,
            'finish' => $this->finish,
        ];
    }
}
