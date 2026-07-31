<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\OpenApi;

interface HttpFetcher
{
    public function get(string $url): string;
}
