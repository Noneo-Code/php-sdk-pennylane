<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Journals;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostJournals extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $code,
        private readonly string $label,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/journals';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
        ];
    }
}
