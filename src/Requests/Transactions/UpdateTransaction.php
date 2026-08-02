<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Transactions;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class UpdateTransaction extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $id,
        private readonly ?int $customerId = null,
        private readonly ?int $supplierId = null,
    ) {
        if (null === $customerId && null === $supplierId) {
            throw new \InvalidArgumentException('La mise à jour d\'une transaction Pennylane requiert un "customerId" ou un "supplierId".');
        }

        if (null !== $customerId && null !== $supplierId) {
            throw new \InvalidArgumentException('La mise à jour d\'une transaction Pennylane accepte un "customerId" ou un "supplierId", pas les deux.');
        }
    }

    public function resolveEndpoint(): string
    {
        return "/transactions/{$this->id}";
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return null !== $this->customerId
            ? ['customer_id' => $this->customerId]
            : ['supplier_id' => $this->supplierId];
    }
}
