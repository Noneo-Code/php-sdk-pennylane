<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Tooling\Schema;

use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaLocator;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;
use PHPUnit\Framework\TestCase;

final class SchemaLocatorTest extends TestCase
{
    public function testItFindsTheSuccessResponseSchemaOfAnOperation(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('getProduct', SchemaSource::Response);

        self::assertSame('object', $schema['type']);
        self::assertContains('label', self::propertyNames($schema));
    }

    public function testItFindsTheRequestBodySchemaOfAnOperation(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('postProducts', SchemaSource::Request);

        self::assertSame(['label'], self::propertyNames($schema));
    }

    /**
     * Un `201` est un succès au même titre qu'un `200` : c'est ce que rendent
     * la plupart des créations Pennylane.
     */
    public function testItAcceptsAnyTwoHundredStatusAsTheSuccessResponse(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('postProducts', SchemaSource::Response);

        self::assertSame(['id'], self::propertyNames($schema));
    }

    public function testItSearchesEverySpecItWasGiven(): void
    {
        $locator = new SchemaLocator([self::spec(), self::firmSpec()]);

        $schema = $locator->locate('getFirmCompany', SchemaSource::Response);

        self::assertSame(['reg_no'], self::propertyNames($schema));
    }

    /**
     * Les sous-DTO n'ont pas d'opération à eux : on les atteint par le chemin
     * de la propriété qui les porte dans la réponse.
     */
    public function testAPointerDescendsIntoAProperty(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('getProduct', SchemaSource::Response, 'ledger_account');

        self::assertSame(['number'], self::propertyNames($schema));
    }

    /**
     * Une liste porte le schéma de ses éléments dans `items` : le pointeur
     * traverse le tableau sans qu'on ait à le dire, parce qu'une fixture décrit
     * toujours un élément, jamais la collection.
     */
    public function testAPointerCrossesAnArrayToReachItsItemSchema(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('getProducts', SchemaSource::Response, 'items');

        self::assertSame(['label'], self::propertyNames($schema));
    }

    /**
     * Les envois de fichiers passent en `multipart/form-data` : leurs champs
     * annexes — dont les énumérations que le SDK reprend en enum — n'ont pas
     * d'autre schéma à confronter.
     */
    public function testItFindsTheRequestBodySchemaOfAMultipartOperation(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('createEInvoiceImport', SchemaSource::Request, 'type');

        self::assertSame(['customer', 'supplier'], $schema['enum']);
    }

    public function testAPointerFindsAPropertyHiddenInsideAOneOfBranch(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('getCustomer', SchemaSource::Response, 'billing_address');

        self::assertSame(['city'], self::propertyNames($schema));
    }

    /**
     * Pennylane discrimine ses `oneOf` par un champ de statut : plusieurs
     * branches portent le même nom de propriété avec un contenu différent.
     * Un segment `champ=valeur` désigne la branche voulue — par son
     * discriminant, pas par son rang, qu'une réécriture de spec changerait.
     */
    public function testASelectorPicksTheOneOfBranchCarryingAGivenDiscriminant(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $schema = $locator->locate('putEInvoiceStatus', SchemaSource::Request, 'status=refused.reason');

        self::assertSame(['bank_details_error'], $schema['enum']);
    }

    public function testASelectorThatMatchesNoBranchIsReported(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Chemin "status=cancelled.reason" introuvable dans le schéma de "putEInvoiceStatus".');

        $locator->locate('putEInvoiceStatus', SchemaSource::Request, 'status=cancelled.reason');
    }

    public function testItRejectsAnUnknownOperationId(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Opération "getUnicorn" absente des specs OpenAPI.');

        $locator->locate('getUnicorn', SchemaSource::Response);
    }

    public function testItRejectsAPointerThatLeadsNowhere(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Chemin "ledger_account.iban" introuvable dans le schéma de "getProduct".');

        $locator->locate('getProduct', SchemaSource::Response, 'ledger_account.iban');
    }

    public function testItRejectsAnOperationWithoutTheRequestedSchema(): void
    {
        $locator = new SchemaLocator([self::spec()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('L\'opération "getProduct" n\'a pas de corps de requête documenté.');

        $locator->locate('getProduct', SchemaSource::Request);
    }

    /**
     * @param mixed[] $schema
     *
     * @return list<string>
     */
    private static function propertyNames(array $schema): array
    {
        self::assertIsArray($schema['properties'] ?? null, 'Le schéma localisé devait porter des propriétés.');

        return array_map(strval(...), array_keys($schema['properties']));
    }

    /**
     * @return array<string, mixed>
     */
    private static function spec(): array
    {
        return ['paths' => [
            '/api/external/v2/products/{id}' => [
                'get' => [
                    'operationId' => 'getProduct',
                    'responses' => ['200' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'ledger_account' => ['type' => 'object', 'properties' => ['number' => ['type' => 'string']]],
                        ],
                    ]]]]],
                ],
            ],
            '/api/external/v2/products' => [
                'get' => [
                    'operationId' => 'getProducts',
                    'responses' => ['200' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'items' => ['type' => 'array', 'items' => [
                                'type' => 'object',
                                'properties' => ['label' => ['type' => 'string']],
                            ]],
                        ],
                    ]]]]],
                ],
                'post' => [
                    'operationId' => 'postProducts',
                    'requestBody' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => ['label' => ['type' => 'string']],
                    ]]]],
                    'responses' => ['201' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => ['id' => ['type' => 'integer']],
                    ]]]]],
                ],
            ],
            '/api/external/v2/supplier_invoices/{id}/e_invoice_status' => [
                'put' => [
                    'operationId' => 'putEInvoiceStatus',
                    'requestBody' => ['content' => ['application/json' => ['schema' => ['oneOf' => [
                        ['type' => 'object', 'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['disputed']],
                            'reason' => ['type' => 'string', 'enum' => ['delivery_issue']],
                        ]],
                        ['type' => 'object', 'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['refused']],
                            'reason' => ['type' => 'string', 'enum' => ['bank_details_error']],
                        ]],
                    ]]]]],
                ],
            ],
            '/api/external/v2/e-invoices/imports' => [
                'post' => [
                    'operationId' => 'createEInvoiceImport',
                    'requestBody' => ['content' => ['multipart/form-data' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => ['type' => 'string', 'format' => 'binary'],
                            'type' => ['type' => 'string', 'enum' => ['customer', 'supplier']],
                        ],
                    ]]]],
                ],
            ],
            '/api/external/v2/customers/{id}' => [
                'get' => [
                    'operationId' => 'getCustomer',
                    'responses' => ['200' => ['content' => ['application/json' => ['schema' => ['oneOf' => [
                        ['type' => 'object', 'properties' => ['first_name' => ['type' => 'string']]],
                        ['type' => 'object', 'properties' => [
                            'billing_address' => ['type' => 'object', 'properties' => ['city' => ['type' => 'string']]],
                        ]],
                    ]]]]]],
                ],
            ],
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function firmSpec(): array
    {
        return ['paths' => [
            '/api/external/firm/v1/companies/{id}' => [
                'get' => [
                    'operationId' => 'getFirmCompany',
                    'responses' => ['200' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => ['reg_no' => ['type' => 'string']],
                    ]]]]],
                ],
            ],
        ]];
    }
}
