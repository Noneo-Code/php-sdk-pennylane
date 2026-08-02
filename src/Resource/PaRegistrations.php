<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\PaRegistration\PaRegistrationDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Requests\PaRegistrations\GetPaRegistrations;
use Saloon\Http\BaseResource;

final class PaRegistrations extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(): CursorPage
    {
        $rawPage = CursorPage::fromArray($this->connector->send(new GetPaRegistrations())->json());

        return new CursorPage(
            array_map(static fn (mixed $item): PaRegistrationDto => PaRegistrationDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }
}
