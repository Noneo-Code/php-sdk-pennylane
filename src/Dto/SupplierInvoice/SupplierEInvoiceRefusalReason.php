<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

/**
 * Motifs recevables pour refuser une facture fournisseur électronique.
 * Distincts de ceux d'une contestation : voir SupplierEInvoiceDisputeReason.
 */
enum SupplierEInvoiceRefusalReason: string
{
    case IncorrectVatRate = 'incorrect_vat_rate';
    case ContractCompleted = 'contract_completed';
    case DuplicateInvoice = 'duplicate_invoice';
    case RecipientError = 'recipient_error';
    case IncorrectPrices = 'incorrect_prices';
    case NonCompliantInvoice = 'non_compliant_invoice';
}
