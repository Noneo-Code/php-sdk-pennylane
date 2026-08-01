<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

enum ImportSourceConnector: string
{
    case Dropbox = 'dropbox';
    case Gdrive = 'gdrive';
}
