<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http\Connector;

use Noneo\Sdk\Pennylane\Http\Connector\CompanyScopeConnector;
use Noneo\Sdk\Pennylane\Http\Connector\FirmConnector;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;

final class FirmConnectorTest extends TestCase
{
    public function testResolvesTheFirmBaseUrl(): void
    {
        $firm = new FirmConnector('firm-token');

        self::assertSame('https://app.pennylane.com/api/external/firm/v1', $firm->resolveBaseUrl());
    }

    public function testScopesTheBaseUrlToACompany(): void
    {
        $scope = new CompanyScopeConnector(new FirmConnector('firm-token'), 123);

        self::assertSame(
            'https://app.pennylane.com/api/external/firm/v1/companies/123',
            $scope->resolveBaseUrl(),
        );
    }

    public function testTheScopeInheritsTheFirmMockClient(): void
    {
        $firm = new FirmConnector('firm-token');
        $mockClient = new MockClient([]);
        $firm->withMockClient($mockClient);

        $scope = new CompanyScopeConnector($firm, 123);

        self::assertSame($mockClient, $scope->getMockClient());
    }
}
