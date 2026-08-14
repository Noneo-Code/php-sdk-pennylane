<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportDto;
use Noneo\Sdk\Pennylane\Dto\EInvoice\EInvoiceImportType;
use Noneo\Sdk\Pennylane\Requests\EInvoices\CreateEInvoiceImport;
use Saloon\Http\BaseResource;

final class EInvoices extends BaseResource
{
    /**
     * Importe une facture électronique sans passer par la ressource client ou
     * fournisseur : le sens est porté par `$type`. Pennylane marque l'opération
     * dépréciée dans sa spec.
     *
     * @param resource|string $fileContents
     *
     * @deprecated au profit de `customerInvoices()->importEInvoice()` et de
     *             `supplierInvoices()->importEInvoice()`, seuls à accepter les
     *             options d'import
     */
    public function import(mixed $fileContents, EInvoiceImportType $type, string $filename): EInvoiceImportDto
    {
        $request = new CreateEInvoiceImport($fileContents, $type, $filename);

        return EInvoiceImportDto::fromArray($this->connector->send($request)->json());
    }
}
