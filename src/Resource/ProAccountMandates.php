<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationCandidateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateMigrationDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\MandateType;
use Noneo\Sdk\Pennylane\Dto\Mandate\ProAccountMandateDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\GetProAccountMandateMigrations;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\GetProAccountMandates;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\PostProAccountMandateMailRequests;
use Noneo\Sdk\Pennylane\Requests\ProAccountMandates\PostProAccountMandateMigrations;
use Saloon\Http\BaseResource;

final class ProAccountMandates extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetProAccountMandates($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): ProAccountMandateDto => ProAccountMandateDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, ProAccountMandateDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetProAccountMandates(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield ProAccountMandateDto::fromArray(self::asArray($item));
        }
    }

    public function migrationCandidates(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetProAccountMandateMigrations($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): MandateMigrationCandidateDto => MandateMigrationCandidateDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, MandateMigrationCandidateDto> */
    public function paginateMigrationCandidates(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetProAccountMandateMigrations(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield MandateMigrationCandidateDto::fromArray(self::asArray($item));
        }
    }

    public function migrate(MandateType $mandateType, int $mandateId, ?bool $earlyExecutionDatePermitted = null): MandateMigrationDto
    {
        $request = new PostProAccountMandateMigrations($mandateType, $mandateId, $earlyExecutionDatePermitted);
        $body = $this->connector->send($request)->json();

        return MandateMigrationDto::fromArray(self::asArray($body['mandate_migration'] ?? null));
    }

    public function sendMandateRequest(int $customerId): void
    {
        $this->connector->send(new PostProAccountMandateMailRequests($customerId));
    }
}
