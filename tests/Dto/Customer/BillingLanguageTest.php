<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use PHPUnit\Framework\TestCase;

final class BillingLanguageTest extends TestCase
{
    public function testItExposesTheThreeDocumentedLanguages(): void
    {
        self::assertSame('fr_FR', BillingLanguage::FrFr->value);
        self::assertSame('en_GB', BillingLanguage::EnGb->value);
        self::assertSame('de_DE', BillingLanguage::DeDe->value);
    }
}
