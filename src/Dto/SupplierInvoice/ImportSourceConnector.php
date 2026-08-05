<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;

#[DocumentedBy('getSupplierInvoice', pointer: 'import_source.source')]
enum ImportSourceConnector: string
{
    case Dropbox = 'dropbox';
    case Gdrive = 'gdrive';
}
