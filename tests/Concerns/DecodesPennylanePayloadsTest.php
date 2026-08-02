<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Concerns;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use PHPUnit\Framework\TestCase;

final class DecodesPennylanePayloadsTest extends TestCase
{
    public function testReturnsTheItemWhenItIsAnArray(): void
    {
        self::assertSame(['id' => 42], PayloadDecoder::decode(['id' => 42]));
    }

    public function testRejectsAScalarItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Un item de la réponse Pennylane doit être un objet.');

        PayloadDecoder::decode('pas un objet');
    }

    public function testRejectsANullItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PayloadDecoder::decode(null);
    }
}

final class PayloadDecoder
{
    use DecodesPennylanePayloads;

    /**
     * @return mixed[]
     */
    public static function decode(mixed $item): array
    {
        return self::asArray($item);
    }
}
