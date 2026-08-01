<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoiceTemplateDto;
use PHPUnit\Framework\TestCase;

final class CustomerInvoiceTemplateDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $template = CustomerInvoiceTemplateDto::fromArray([
            'id' => 42,
            'name' => 'Standard Invoice Template',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(42, $template->id);
        self::assertSame('Standard Invoice Template', $template->name);
    }
}
