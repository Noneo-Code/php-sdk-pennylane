<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Dms\DmsFileDto;
use Noneo\Sdk\Pennylane\Dto\Dms\DmsFolderDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\GetDmsFiles;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\ListDmsFolders;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\PostDmsFiles;
use Noneo\Sdk\Pennylane\Requests\Firm\Dms\PostDmsFolders;
use Saloon\Http\BaseResource;

final class Dms extends BaseResource
{
    public function files(?string $cursor = null, ?int $limit = null, ?FilterSet $filters = null): CursorPage
    {
        $response = $this->connector->send(new GetDmsFiles($cursor, $limit, $filters));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): DmsFileDto => DmsFileDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, DmsFileDto> */
    public function paginateFiles(?FilterSet $filters = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetDmsFiles(filters: $filters), filters: $filters);

        foreach ($paginator->items() as $item) {
            yield DmsFileDto::fromArray(self::asArray($item));
        }
    }

    /** @param resource|string $fileContents */
    public function uploadFile(mixed $fileContents, ?string $name = null, ?int $parentFolderId = null): DmsFileDto
    {
        $request = new PostDmsFiles($fileContents, $name, $parentFolderId);

        return DmsFileDto::fromArray($this->connector->send($request)->json());
    }

    public function folders(?string $cursor = null, ?int $limit = null, ?FilterSet $filters = null): CursorPage
    {
        $response = $this->connector->send(new ListDmsFolders($cursor, $limit, $filters));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): DmsFolderDto => DmsFolderDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, DmsFolderDto> */
    public function paginateFolders(?FilterSet $filters = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new ListDmsFolders(filters: $filters), filters: $filters);

        foreach ($paginator->items() as $item) {
            yield DmsFolderDto::fromArray(self::asArray($item));
        }
    }

    public function createFolder(string $name, ?int $parentFolderId = null): DmsFolderDto
    {
        return DmsFolderDto::fromArray($this->connector->send(new PostDmsFolders($name, $parentFolderId))->json());
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
