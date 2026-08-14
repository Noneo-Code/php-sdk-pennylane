<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\EInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

/**
 * Sens de circulation de la facture importée par l'endpoint générique
 * `/e-invoices/imports` : côté client ou côté fournisseur. Les endpoints
 * spécialisés qui lui succèdent portent ce choix dans leur chemin.
 */
#[DocumentedBy('createEInvoiceImport', pointer: 'type', source: SchemaSource::Request)]
enum EInvoiceImportType: string
{
    case Customer = 'customer';
    case Supplier = 'supplier';
}
