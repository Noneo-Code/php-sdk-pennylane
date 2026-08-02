<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Export\AnalyticalGeneralLedgerExportMode;
use Noneo\Sdk\Pennylane\Dto\Export\ExportDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportFileDto;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportAnalyticalGeneralLedger;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportFec;
use Noneo\Sdk\Pennylane\Requests\Exports\ExportGeneralLedger;
use Noneo\Sdk\Pennylane\Requests\Exports\GetAnalyticalGeneralLedgerExport;
use Noneo\Sdk\Pennylane\Requests\Exports\GetFecExport;
use Noneo\Sdk\Pennylane\Requests\Exports\GetGeneralLedgerExport;
use Saloon\Http\BaseResource;

final class Exports extends BaseResource
{
    use DecodesPennylanePayloads;

    public function exportFec(string $periodStart, string $periodEnd): ExportDto
    {
        $request = new ExportFec($periodStart, $periodEnd);

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getFecExport(int $id): ExportFileDto
    {
        return ExportFileDto::fromArray(self::asArray($this->connector->send(new GetFecExport($id))->json()));
    }

    public function exportGeneralLedger(string $periodStart, string $periodEnd): ExportDto
    {
        $request = new ExportGeneralLedger($periodStart, $periodEnd);

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getGeneralLedgerExport(int $id): ExportFileDto
    {
        return ExportFileDto::fromArray(self::asArray($this->connector->send(new GetGeneralLedgerExport($id))->json()));
    }

    public function exportAnalyticalGeneralLedger(
        string $periodStart,
        string $periodEnd,
        ?AnalyticalGeneralLedgerExportMode $mode = null,
    ): ExportDto {
        $request = new ExportAnalyticalGeneralLedger($periodStart, $periodEnd, $mode);

        return ExportDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }

    public function getAnalyticalGeneralLedgerExport(int $id): ExportFileDto
    {
        $request = new GetAnalyticalGeneralLedgerExport($id);

        return ExportFileDto::fromArray(self::asArray($this->connector->send($request)->json()));
    }
}
