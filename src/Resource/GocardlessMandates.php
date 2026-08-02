<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Mandate\GocardlessMandateDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\GetGocardlessMandate;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\GetGocardlessMandates;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateAssociations;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateCancellations;
use Noneo\Sdk\Pennylane\Requests\GocardlessMandates\PostGocardlessMandateMailRequests;
use Saloon\Http\BaseResource;

final class GocardlessMandates extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetGocardlessMandates($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): GocardlessMandateDto => GocardlessMandateDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, GocardlessMandateDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetGocardlessMandates(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield GocardlessMandateDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): GocardlessMandateDto
    {
        return GocardlessMandateDto::fromArray($this->connector->send(new GetGocardlessMandate($id))->json());
    }

    /** @param list<string> $recipients */
    public function sendMailRequest(int $customerId, array $recipients, ?string $subject = null, ?string $emailBody = null): void
    {
        $this->connector->send(new PostGocardlessMandateMailRequests($customerId, $recipients, $subject, $emailBody));
    }

    public function associate(int $gocardlessMandateId, int $customerId): void
    {
        $this->connector->send(new PostGocardlessMandateAssociations($gocardlessMandateId, $customerId));
    }

    public function cancel(int $gocardlessMandateId): void
    {
        $this->connector->send(new PostGocardlessMandateCancellations($gocardlessMandateId));
    }
}
