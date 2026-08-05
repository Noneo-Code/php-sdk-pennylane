<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Schema;

/**
 * Rattache une fixture de test — ou un enum du SDK — au schéma OpenAPI dont
 * elle prétend être un exemple.
 *
 * Sans ce lien, rien ne relie un `documentedPayload()` recopié à la main à
 * l'opération qu'il illustre : le nom de la classe de test ne suffit pas, les
 * `operationId` de Pennylane ne suivant aucune convention dérivable
 * (`company-fiscal-years` côté cabinet). L'attribut le dit explicitement, à
 * l'endroit exact où la fixture vit.
 *
 * Sur un enum, l'attribut désigne le champ dont il reprend l'énumération :
 *
 *     #[DocumentedBy('getCustomer', pointer: 'payment_conditions')]
 *     enum PaymentConditions: string { … }
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
final class DocumentedBy
{
    /**
     * @param string       $operationId `operationId` de l'opération, tel qu'écrit dans la spec
     * @param string       $pointer     chemin de propriétés jusqu'au schéma visé, séparées par
     *                                  des points ; vide pour le corps entier. Les tableaux se
     *                                  traversent d'eux-mêmes : `items` désigne un élément de
     *                                  liste, pas la liste
     * @param SchemaSource $source      côté de l'échange : ce que l'API rend, ou ce qu'elle accepte
     */
    public function __construct(
        public readonly string $operationId,
        public readonly string $pointer = '',
        public readonly SchemaSource $source = SchemaSource::Response,
    ) {
    }
}
