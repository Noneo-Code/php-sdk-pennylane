<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\ProAccountMandates;

use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostProAccountMandateMigrations extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly MandateType $mandateType,
        private readonly int $mandateId,
        private readonly ?bool $earlyExecutionDatePermitted = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/pro_account/mandate_migrations';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'mandate_type' => $this->mandateType->value,
            'mandate_id' => $this->mandateId,
            'early_execution_date_permitted' => $this->earlyExecutionDatePermitted,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
