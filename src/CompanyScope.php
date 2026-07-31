<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class CompanyScope
{
    public function __construct(
        private readonly PennylaneFirm $connector,
        private readonly int $companyId,
    ) {
    }

    public function send(Request $request): Response
    {
        $request->middleware()->onRequest(
            fn (PendingRequest $pendingRequest) => $pendingRequest->setUrl($this->scopeUrl($pendingRequest->getUrl())),
        );

        return $this->connector->send($request);
    }

    private function scopeUrl(string $url): string
    {
        $base = rtrim($this->connector->resolveBaseUrl(), '/');

        return $base.'/companies/'.$this->companyId.substr($url, strlen($base));
    }
}
