<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\LedgerEntryLines;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\UnbalancedLetteringStrategy;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class PostLedgerEntryLinesLetter extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<int> $ledgerEntryLineIds */
    public function __construct(
        private readonly UnbalancedLetteringStrategy $unbalancedLetteringStrategy,
        private readonly array $ledgerEntryLineIds,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/ledger_entry_lines/lettering';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return [
            'unbalanced_lettering_strategy' => $this->unbalancedLetteringStrategy->value,
            'ledger_entry_lines' => array_map(
                static fn (int $id): array => ['id' => $id],
                $this->ledgerEntryLineIds,
            ),
        ];
    }
}
