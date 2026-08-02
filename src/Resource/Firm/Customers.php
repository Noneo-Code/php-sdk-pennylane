<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\Customer\CompanyCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\IndividualCustomerDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomers;
use Saloon\Http\BaseResource;

final class Customers extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetCustomers($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = self::customerFromArray($item);
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CustomerDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomers(filters: $filters, sort: $sort), filters: $filters, sort: $sort);

        foreach ($paginator->items() as $item) {
            yield self::customerFromArray($item);
        }
    }

    private static function customerFromArray(mixed $item): CustomerDto
    {
        $item = self::asArray($item);
        $type = $item['customer_type'] ?? null;

        return match ($type) {
            'company' => CompanyCustomerDto::fromArray($item),
            'individual' => IndividualCustomerDto::fromArray($item),
            default => throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "customer_type" valant "company" ou "individual".'),
        };
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
