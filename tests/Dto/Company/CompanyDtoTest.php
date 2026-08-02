<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Company;

use Noneo\Sdk\Pennylane\Dto\Company\CompanyDto;
use PHPUnit\Framework\TestCase;

final class CompanyDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $dto = CompanyDto::fromArray([
            'id' => 22938,
            'name' => 'Koss Inc.',
            'billing_company_name' => 'Koss',
            'siren' => '362521879',
            'address' => '35508 Mary Plain',
            'city' => 'Jefferson City',
            'postal_code' => '77379',
            'activity_nomenclature' => 'NAFRev2',
            'activity_code' => '1234A',
            'external_id' => 'G6YYYLPXIZ',
            'client_code' => 'DOSSIER_ABC',
        ]);

        self::assertSame(22938, $dto->id);
        self::assertSame('Koss Inc.', $dto->name);
        self::assertSame('Koss', $dto->billingCompanyName);
        self::assertSame('362521879', $dto->siren);
        self::assertSame('35508 Mary Plain', $dto->address);
        self::assertSame('Jefferson City', $dto->city);
        self::assertSame('77379', $dto->postalCode);
        self::assertSame('NAFRev2', $dto->activityNomenclature);
        self::assertSame('1234A', $dto->activityCode);
        self::assertSame('G6YYYLPXIZ', $dto->externalId);
        self::assertSame('DOSSIER_ABC', $dto->clientCode);
    }
}
