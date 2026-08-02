<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateDto;
use Noneo\Sdk\Pennylane\Dto\Mandate\SepaMandateSequenceType;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\DeleteSepaMandate;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\GetSepaMandate;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\GetSepaMandates;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\PostSepaMandates;
use Noneo\Sdk\Pennylane\Requests\SepaMandates\PutSepaMandate;
use Saloon\Http\BaseResource;

final class SepaMandates extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetSepaMandates($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): SepaMandateDto => SepaMandateDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, SepaMandateDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetSepaMandates(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield SepaMandateDto::fromArray(self::asArray($item));
        }
    }

    public function create(
        string $bic,
        string $iban,
        string $signedAt,
        string $identifier,
        int $customerId,
        ?string $bank = null,
        ?SepaMandateSequenceType $sequenceType = null,
    ): SepaMandateDto {
        $request = new PostSepaMandates($bic, $iban, $signedAt, $identifier, $customerId, $bank, $sequenceType);

        return SepaMandateDto::fromArray($this->connector->send($request)->json());
    }

    public function get(int $id): SepaMandateDto
    {
        return SepaMandateDto::fromArray($this->connector->send(new GetSepaMandate($id))->json());
    }

    public function update(
        int $id,
        string|Clear|null $bank = null,
        string|Clear|null $bic = null,
        string|Clear|null $iban = null,
        SepaMandateSequenceType|Clear|null $sequenceType = null,
        string|Clear|null $signedAt = null,
        string|Clear|null $identifier = null,
        int|Clear|null $customerId = null,
    ): SepaMandateDto {
        $request = new PutSepaMandate($id, $bank, $bic, $iban, $sequenceType, $signedAt, $identifier, $customerId);

        return SepaMandateDto::fromArray($this->connector->send($request)->json());
    }

    public function delete(int $id): void
    {
        $this->connector->send(new DeleteSepaMandate($id));
    }
}
