<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\Http\ChangelogPaginator;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Requests\Changelogs\ChangelogRequest;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetDmsFilesChanges;
use Noneo\Sdk\Pennylane\Requests\Changelogs\GetLedgerEntryLineChanges;
use Saloon\Http\BaseResource;

final class Changelogs extends BaseResource
{
    use DecodesPennylanePayloads;

    public function ledgerEntryLines(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetLedgerEntryLineChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateLedgerEntryLines(?string $startDate = null, ?int $perPage = null): \Generator
    {
        yield from $this->paginateUniform(new GetLedgerEntryLineChanges(startDate: $startDate), $perPage);
    }

    public function dmsFiles(?string $cursor = null, ?int $limit = null, ?string $startDate = null): CursorPage
    {
        return $this->uniformPage(new GetDmsFilesChanges($cursor, $limit, $startDate));
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    public function paginateDmsFiles(?string $startDate = null, ?int $perPage = null): \Generator
    {
        yield from $this->paginateUniform(new GetDmsFilesChanges(startDate: $startDate), $perPage);
    }

    private function uniformPage(ChangelogRequest $request): CursorPage
    {
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): ChangelogEntryDto => ChangelogEntryDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, ChangelogEntryDto> */
    private function paginateUniform(ChangelogRequest $request, ?int $perPage = null): \Generator
    {
        $paginator = new ChangelogPaginator($this->connector, $request, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield ChangelogEntryDto::fromArray(self::asArray($item));
        }
    }
}
