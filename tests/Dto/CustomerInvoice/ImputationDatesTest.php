<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImputationDates;
use PHPUnit\Framework\TestCase;

final class ImputationDatesTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $dates = ImputationDates::fromArray(['start_date' => '2020-06-30', 'end_date' => '2021-06-30']);

        self::assertSame('2020-06-30', $dates->startDate);
        self::assertSame('2021-06-30', $dates->endDate);
    }

    public function testToArrayRoundTrips(): void
    {
        $dates = new ImputationDates('2020-06-30', '2021-06-30');

        self::assertSame(['start_date' => '2020-06-30', 'end_date' => '2021-06-30'], $dates->toArray());
    }
}
