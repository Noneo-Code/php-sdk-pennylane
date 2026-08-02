<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Me\MeDto;
use Noneo\Sdk\Pennylane\Requests\Me\GetMe;
use Saloon\Http\BaseResource;

final class Me extends BaseResource
{
    public function get(): MeDto
    {
        return MeDto::fromArray($this->connector->send(new GetMe())->json());
    }
}
