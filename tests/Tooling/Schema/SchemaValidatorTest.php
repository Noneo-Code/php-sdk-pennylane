<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;

final class SchemaValidatorTest extends TestCase
{
    public function testItAcceptsAPayloadThatMatchesTheSchema(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
            ],
            'required' => ['id'],
        ];

        self::assertSame([], SchemaValidator::validate(['id' => 42, 'name' => 'Ada'], $schema));
    }

    public function testItReportsAFieldTheSchemaDoesNotDeclare(): void
    {
        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => ['id' => ['type' => 'integer']],
        ];

        self::assertSame(
            ['$.probable_field : champ absent du schéma.'],
            SchemaValidator::validate(['id' => 42, 'probable_field' => 'inventé'], $schema),
        );
    }

    /**
     * Le cœur du sujet : une fixture recopiée à la main peut porter un champ
     * plausible mais inexistant. Le schéma le dit, à condition de comparer.
     */
    public function testItReportsAnUnknownFieldEvenWhenAdditionalPropertiesIsUnset(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'integer']],
        ];

        self::assertSame(
            ['$.inventé : champ absent du schéma.'],
            SchemaValidator::validate(['id' => 42, 'inventé' => true], $schema),
        );
    }

    public function testItReportsAMissingRequiredField(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string']],
            'required' => ['id', 'name'],
        ];

        self::assertSame(
            ['$.name : champ requis absent du payload.'],
            SchemaValidator::validate(['id' => 42], $schema),
        );
    }

    public function testItReportsAMistypedField(): void
    {
        $schema = ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]];

        self::assertSame(
            ['$.id : entier attendu, texte reçu.'],
            SchemaValidator::validate(['id' => '42'], $schema),
        );
    }

    public function testItAcceptsAnIntegerWhereANumberIsExpected(): void
    {
        $schema = ['type' => 'object', 'properties' => ['ratio' => ['type' => 'number']]];

        self::assertSame([], SchemaValidator::validate(['ratio' => 3], $schema));
    }

    public function testItAcceptsNullOnANullableField(): void
    {
        $schema = ['type' => 'object', 'properties' => ['label' => ['type' => 'string', 'nullable' => true]]];

        self::assertSame([], SchemaValidator::validate(['label' => null], $schema));
    }

    public function testItReportsNullOnAFieldThatIsNotNullable(): void
    {
        $schema = ['type' => 'object', 'properties' => ['label' => ['type' => 'string']]];

        self::assertSame(
            ['$.label : champ non nullable valué à null.'],
            SchemaValidator::validate(['label' => null], $schema),
        );
    }

    public function testItReportsAValueOutsideTheDeclaredEnum(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['status' => ['type' => 'string', 'enum' => ['draft', 'paid']]],
        ];

        self::assertSame(
            ['$.status : valeur "cancelled" hors de l\'énumération (draft, paid).'],
            SchemaValidator::validate(['status' => 'cancelled'], $schema),
        );
    }

    public function testItWalksIntoNestedObjects(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'address' => [
                    'type' => 'object',
                    'properties' => ['city' => ['type' => 'string']],
                ],
            ],
        ];

        self::assertSame(
            ['$.address.city : texte attendu, entier reçu.'],
            SchemaValidator::validate(['address' => ['city' => 75002]], $schema),
        );
    }

    public function testItWalksIntoArrayItemsAndPointsAtTheOffendingIndex(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'lines' => [
                    'type' => 'array',
                    'items' => ['type' => 'object', 'properties' => ['label' => ['type' => 'string']]],
                ],
            ],
        ];

        self::assertSame(
            ['$.lines[1].label : texte attendu, entier reçu.'],
            SchemaValidator::validate(['lines' => [['label' => 'ok'], ['label' => 7]]], $schema),
        );
    }

    /**
     * `allOf` sert surtout, dans la spec Pennylane, à accrocher une description
     * à un type déjà défini : les branches se cumulent.
     */
    public function testItAppliesEveryBranchOfAnAllOf(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'substance' => [
                    'allOf' => [
                        ['type' => 'string', 'enum' => ['goods', 'services']],
                        ['description' => 'Résout le compte comptable de la ligne.'],
                    ],
                ],
            ],
        ];

        self::assertSame([], SchemaValidator::validate(['substance' => 'goods'], $schema));
        self::assertSame(
            ['$.substance : valeur "other" hors de l\'énumération (goods, services).'],
            SchemaValidator::validate(['substance' => 'other'], $schema),
        );
    }

    public function testItAcceptsAPayloadMatchingAnyBranchOfAOneOf(): void
    {
        $schema = ['oneOf' => [
            ['type' => 'object', 'properties' => ['company_name' => ['type' => 'string']], 'required' => ['company_name']],
            ['type' => 'object', 'properties' => ['first_name' => ['type' => 'string']], 'required' => ['first_name']],
        ]];

        self::assertSame([], SchemaValidator::validate(['first_name' => 'Ada'], $schema));
    }

    /**
     * Quand aucune branche ne passe, rapporter les écarts de *toutes* les
     * branches noierait le signal : on rend ceux de la branche la plus proche,
     * qui est presque toujours celle que la fixture visait.
     */
    public function testItReportsTheClosestBranchWhenNoOneOfBranchMatches(): void
    {
        $schema = ['oneOf' => [
            ['type' => 'object', 'properties' => ['company_name' => ['type' => 'string']], 'required' => ['company_name']],
            ['type' => 'object', 'properties' => ['first_name' => ['type' => 'string'], 'last_name' => ['type' => 'string']], 'required' => ['first_name', 'last_name']],
        ]];

        self::assertSame(
            ['$.last_name : champ requis absent du payload.'],
            SchemaValidator::validate(['first_name' => 'Ada'], $schema),
        );
    }

    public function testItReportsEveryDivergenceRatherThanStoppingAtTheFirst(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string']],
            'required' => ['name'],
        ];

        self::assertSame(
            [
                '$.id : entier attendu, texte reçu.',
                '$.name : champ requis absent du payload.',
                '$.extra : champ absent du schéma.',
            ],
            SchemaValidator::validate(['id' => 'x', 'extra' => 1], $schema),
        );
    }

    /**
     * Un schéma sans `type` ni combinateur ne contraint rien — la spec en a
     * pour les champs libres. Il ne doit rien rapporter.
     */
    public function testItAcceptsAnythingAgainstAnUnconstrainedSchema(): void
    {
        self::assertSame([], SchemaValidator::validate(['tout' => ['ce' => 'qu\'on veut']], []));
    }
}
