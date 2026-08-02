<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Export\AccountNumbersType;
use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Noneo\Sdk\Pennylane\Dto\Export\ExportDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportFileDto;
use Noneo\Sdk\Pennylane\Dto\Export\SoftwareSpecificExportType;
use Noneo\Sdk\Pennylane\Requests\Exports\GetAnalyticalGeneralLedgerExport;
use Noneo\Sdk\Pennylane\Requests\Exports\GetFecExport;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\GetSoftwareSpecificEntriesExport;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostAnalyticalGeneralLedgerExport;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostFecExport;
use Noneo\Sdk\Pennylane\Requests\Firm\Exports\PostSoftwareSpecificEntriesExport;
use Saloon\Http\BaseResource;

final class Exports extends BaseResource
{
    public function exportFec(string $periodStart, string $periodEnd): ExportDto
    {
        $request = new PostFecExport($periodStart, $periodEnd);

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getFecExport(int $id): ExportFileDto
    {
        return ExportFileDto::fromArray(self::asArray($this->connector->send(new GetFecExport($id))->json()));
    }

    public function exportAnalyticalGeneralLedger(
        string $periodStart,
        string $periodEnd,
        ?AnalyticalGeneralLedgerExportMode $mode = null,
    ): ExportDto {
        $request = new PostAnalyticalGeneralLedgerExport($periodStart, $periodEnd, $mode);

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getAnalyticalGeneralLedgerExport(int $id): ExportFileDto
    {
        $request = new GetAnalyticalGeneralLedgerExport($id);

        return ExportFileDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    /**
     * Produit un fichier destiné à des logiciels tiers (ACD, AGIRIS) et
     * **sans valeur légale** ; pour un usage DGFIP, utiliser `exportFec()`.
     *
     * @param ?list<string> $journalCodes
     */
    public function exportSoftwareSpecificEntries(
        SoftwareSpecificExportType $exportType,
        string $periodStart,
        string $periodEnd,
        ?array $journalCodes = null,
        ?bool $includeAttachments = null,
        ?AccountNumbersType $accountNumbersType = null,
    ): ExportDto {
        $request = new PostSoftwareSpecificEntriesExport(
            $exportType,
            $periodStart,
            $periodEnd,
            $journalCodes,
            $includeAttachments,
            $accountNumbersType,
        );

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getSoftwareSpecificEntriesExport(int $id): ExportFileDto
    {
        return ExportFileDto::fromArray(self::asArray($this->connector->send(new GetSoftwareSpecificEntriesExport($id))->json()));
    }

    /** @return mixed[] */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
