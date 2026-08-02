<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\Firm\Exports;

use Noneo\Sdk\Pennylane\Dto\Export\AccountNumbersType;
use Noneo\Sdk\Pennylane\Dto\Export\SoftwareSpecificExportType;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Ce fichier est destiné à des logiciels tiers (ACD, AGIRIS) et n'a **aucune
 * valeur légale** ; pour un usage DGFIP, utiliser l'export FEC
 * (`Exports::exportFec()`).
 */
final class PostSoftwareSpecificEntriesExport extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param ?list<string> $journalCodes */
    public function __construct(
        private readonly SoftwareSpecificExportType $exportType,
        private readonly string $periodStart,
        private readonly string $periodEnd,
        private readonly ?array $journalCodes = null,
        private readonly ?bool $includeAttachments = null,
        private readonly ?AccountNumbersType $accountNumbersType = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/exports/software_specific_entries';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'export_type' => $this->exportType->value,
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'journal_codes' => $this->journalCodes,
            'include_attachments' => $this->includeAttachments,
            'account_numbers_type' => $this->accountNumbersType?->value,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
