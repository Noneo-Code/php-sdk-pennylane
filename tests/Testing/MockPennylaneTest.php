<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Testing;

use Noneo\Sdk\Pennylane\Dto\Journal\JournalDto;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\Testing\MockPennylane;
use Noneo\Sdk\Pennylane\Testing\UnexpectedRequestException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * Ces tests consomment le SDK exactement comme un projet utilisateur : aucun
 * import `Saloon\*`, ni accès à `connector()`.
 */
final class MockPennylaneTest extends TestCase
{
    /** @return array<string, mixed> */
    private function journalPayload(): array
    {
        return [
            'code' => 'RB',
            'id' => 42,
            'label' => 'Journal de Reprise Balance',
            'type' => 'miscellaneous',
        ];
    }

    public function testMatchesByMethodAndPath(): void
    {
        $mock = MockPennylane::create([
            'GET /journals/42' => MockPennylane::json($this->journalPayload()),
        ]);

        $journal = $mock->pennylane()->journals()->get(42);

        self::assertSame(42, $journal->id);
        $mock->assertSent('GET', '/journals/42');
    }

    public function testMatchesWithWildcards(): void
    {
        $mock = MockPennylane::create([
            'GET /journals/*' => MockPennylane::json($this->journalPayload()),
        ]);

        $journal = $mock->pennylane()->journals()->get(42);

        self::assertSame('RB', $journal->code);
        $mock->assertSent('GET', '/journals/42');
    }

    public function testServesASequentialQueue(): void
    {
        $mock = MockPennylane::create([
            MockPennylane::json(['code' => 'A', 'id' => 1, 'label' => 'Premier', 'type' => 'sale']),
            MockPennylane::json(['code' => 'B', 'id' => 2, 'label' => 'Second', 'type' => 'purchase']),
        ]);
        $pennylane = $mock->pennylane();

        self::assertSame(1, $pennylane->journals()->get(1)->id);
        self::assertSame(2, $pennylane->journals()->get(2)->id);
    }

    public function testRejectsUnmatchedRequests(): void
    {
        $mock = MockPennylane::create(['GET /products' => MockPennylane::json([])]);

        $this->expectException(UnexpectedRequestException::class);

        $mock->pennylane()->journals()->get(1);
    }

    public function testAnExhaustedQueueRejectsTheNextRequest(): void
    {
        $mock = MockPennylane::create([]);

        $this->expectException(UnexpectedRequestException::class);

        $mock->pennylane()->journals()->get(1);
    }

    public function testErrorResponsesRaiseTheSdkExceptions(): void
    {
        $mock = MockPennylane::create([
            'GET /journals/9' => MockPennylane::error(404),
        ]);

        $this->expectException(NotFoundException::class);

        $mock->pennylane()->journals()->get(9);
    }

    public function testAssertionsFailLoudly(): void
    {
        $mock = MockPennylane::create(['GET /journals/1' => MockPennylane::json($this->journalPayload())]);
        $mock->pennylane()->journals()->get(1);

        $this->expectException(AssertionFailedError::class);

        $mock->assertSent('POST', '/journals');
    }

    public function testAssertNotSentAndAssertNothingSent(): void
    {
        $mock = MockPennylane::create(['GET /journals/1' => MockPennylane::json($this->journalPayload())]);

        $mock->assertNothingSent();

        $mock->pennylane()->journals()->get(1);

        $mock->assertNotSent('POST', '/journals');

        $this->expectException(AssertionFailedError::class);
        $mock->assertNotSent('GET', '/journals/1');
    }

    public function testAssertNothingElseSentSeesUnassertedCalls(): void
    {
        $mock = MockPennylane::create(['GET /journals/1' => MockPennylane::json($this->journalPayload())]);
        $mock->pennylane()->journals()->get(1);

        $this->expectException(AssertionFailedError::class);

        $mock->assertNothingElseSent();
    }

    public function testAssertNothingElseSentPassesOnceEverythingIsAsserted(): void
    {
        $mock = MockPennylane::create(['GET /journals/1' => MockPennylane::json($this->journalPayload())]);
        $mock->pennylane()->journals()->get(1);

        $mock->assertSent('GET', '/journals/1');
        $mock->assertNothingElseSent();
    }

    public function testWorksAgainstTheFirmApi(): void
    {
        $mock = MockPennylane::create([
            'GET /companies/12/journals' => MockPennylane::json([
                'items' => [['id' => 7, 'code' => 'VT', 'label' => 'Ventes', 'type' => 'sale']],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]);

        $page = $mock->firm()->company(12)->journals()->list();

        self::assertInstanceOf(JournalDto::class, $page->items[0]);
        self::assertSame(7, $page->items[0]->id);
        $mock->assertSent('GET', '/companies/12/journals');
    }

    public function testRefusesMixedKeyedAndSequentialResponses(): void
    {
        $this->expectException(\LogicException::class);

        MockPennylane::create([
            'GET /a' => MockPennylane::json([]),
            MockPennylane::json([]),
        ]);
    }

    public function testRefusesAMalformedResponseKey(): void
    {
        $this->expectException(\LogicException::class);

        MockPennylane::create(['journals' => MockPennylane::json([])]);
    }
}
