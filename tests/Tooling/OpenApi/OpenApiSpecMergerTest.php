<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\OpenApi;

use Noneo\Sdk\Pennylane\Tooling\OpenApi\OpenApiSpecMerger;
use PHPUnit\Framework\TestCase;

final class OpenApiSpecMergerTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function fragment(string $path, string $method, string $operationId, string $scope): array
    {
        return [
            'openapi' => '3.0.1',
            'info' => ['title' => 'Company V2', 'version' => '2.0'],
            'servers' => [['url' => 'https://app.pennylane.com']],
            'paths' => [
                $path => [
                    $method => [
                        'operationId' => $operationId,
                        'summary' => ucfirst($operationId),
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'oauth2' => [
                        'type' => 'oauth2',
                        'flows' => [
                            'authorizationCode' => [
                                'authorizationUrl' => 'https://app.pennylane.com/oauth/authorize',
                                'scopes' => [
                                    $scope => "Grant access to {$scope}",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testMergesPathsFromMultipleFragments(): void
    {
        $merger = new OpenApiSpecMerger();
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'get', 'getJournals', 'journals:readonly'));
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'post', 'postJournals', 'journals:all'));

        $spec = $merger->toArray();

        /** @var array<string, mixed> $info */
        $info = $spec['info'];
        /** @var array<string, array<string, array<string, mixed>>> $paths */
        $paths = $spec['paths'];

        self::assertSame('3.0.1', $spec['openapi']);
        self::assertSame('Company V2', $info['title']);
        self::assertSame(
            'getJournals',
            $paths['/api/external/v2/journals']['get']['operationId'],
        );
        self::assertSame(
            'postJournals',
            $paths['/api/external/v2/journals']['post']['operationId'],
        );
    }

    public function testSortsPathsAlphabeticallyAndMethodsInFixedOrder(): void
    {
        $merger = new OpenApiSpecMerger();
        $merger->addFragment($this->fragment('/api/external/v2/suppliers', 'get', 'getSuppliers', 'suppliers:readonly'));
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'post', 'postJournals', 'journals:all'));
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'get', 'getJournals', 'journals:readonly'));

        /** @var array<string, array<string, mixed>> $paths */
        $paths = $merger->toArray()['paths'];

        self::assertSame(
            ['/api/external/v2/journals', '/api/external/v2/suppliers'],
            array_keys($paths),
        );
        self::assertSame(
            ['get', 'post'],
            array_keys($paths['/api/external/v2/journals']),
        );
    }

    public function testUnionsOauthScopesAcrossFragments(): void
    {
        $merger = new OpenApiSpecMerger();
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'get', 'getJournals', 'journals:readonly'));
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'post', 'postJournals', 'journals:all'));

        /** @var array<string, mixed> $components */
        $components = $merger->toArray()['components'];
        /** @var array<string, array<string, array<string, array<string, array<string, string>>>>> $securitySchemes */
        $securitySchemes = $components['securitySchemes'];
        $scopes = $securitySchemes['oauth2']['flows']['authorizationCode']['scopes'];

        self::assertSame(
            ['journals:all', 'journals:readonly'],
            array_keys($scopes),
        );
    }

    public function testThrowsWhenTheSameOperationIsDefinedTwiceDifferently(): void
    {
        $merger = new OpenApiSpecMerger();
        $merger->addFragment($this->fragment('/api/external/v2/journals', 'get', 'getJournals', 'journals:readonly'));

        $this->expectException(\RuntimeException::class);

        $merger->addFragment($this->fragment('/api/external/v2/journals', 'get', 'getJournalsBis', 'journals:readonly'));
    }
}
