<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Le garde-fou anti-float vit sur le connecteur, pas dans les 180 classes de
 * requête : c'est le seul endroit par où passent tous les corps et toutes les
 * query, y compris ceux que les `Request` construisent à la main sans passer
 * par `Payload::build()`.
 */
final class RejectsFloatValuesTest extends TestCase
{
    #[DataProvider('provideConnectors')]
    public function testRefusesToSendAFloatInTheBody(Connector $connector): void
    {
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        try {
            $connector->send($this->postRequest(['amount' => 120.50]));
            self::fail('Un float dans le corps doit interrompre l\'envoi.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('amount', $exception->getMessage());
        }

        self::assertCount(0, $mockClient->getRecordedResponses(), 'Aucune requête ne doit partir.');
    }

    public function testRefusesAFloatNestedInInvoiceLines(): void
    {
        $connector = new Pennylane(new TokenAuthenticator('a-token'));
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invoice_lines[0][raw_currency_unit_price]');

        $connector->send($this->postRequest([
            'invoice_lines' => [['label' => 'Prestation', 'raw_currency_unit_price' => 120.50]],
        ]));
    }

    public function testRefusesAFloatInTheQueryString(): void
    {
        $connector = new Pennylane(new TokenAuthenticator('a-token'));
        $connector->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount');

        $connector->send($this->getRequest(['amount' => 120.50]));
    }

    public function testLetsDecimalStringsAndIntegersThrough(): void
    {
        $connector = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        $connector->send($this->postRequest(['amount' => '120.50', 'quantity' => 3]));

        self::assertSame(200, $mockClient->getLastResponse()?->status());
    }

    /**
     * @return iterable<string, array{Connector}>
     */
    public static function provideConnectors(): iterable
    {
        yield 'company' => [new Pennylane(new TokenAuthenticator('a-token'))];
        yield 'firm' => [new PennylaneFirm(new TokenAuthenticator('a-token'))];
    }

    /** @param array<string, mixed> $fields */
    private function postRequest(array $fields): Request
    {
        return new class($fields) extends Request implements HasBody {
            use HasJsonBody;

            protected Method $method = Method::POST;

            /** @param array<string, mixed> $fields */
            public function __construct(private readonly array $fields)
            {
            }

            public function resolveEndpoint(): string
            {
                return '/customer_invoices';
            }

            /** @return array<string, mixed> */
            protected function defaultBody(): array
            {
                return $this->fields;
            }
        };
    }

    /** @param array<string, mixed> $parameters */
    private function getRequest(array $parameters): Request
    {
        return new class($parameters) extends Request {
            protected Method $method = Method::GET;

            /** @param array<string, mixed> $parameters */
            public function __construct(private readonly array $parameters)
            {
            }

            public function resolveEndpoint(): string
            {
                return '/customer_invoices';
            }

            /** @return array<string, mixed> */
            protected function defaultQuery(): array
            {
                return $this->parameters;
            }
        };
    }
}
