<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use Noneo\Sdk\Pennylane\Tooling\Schema\SchemaSource;

/**
 * Motifs recevables pour contester une facture fournisseur électronique.
 * Distincts de ceux d'un refus : voir SupplierEInvoiceRefusalReason.
 */
#[DocumentedBy('putSupplierInvoiceEInvoiceStatus', pointer: 'status=disputed.reason', source: SchemaSource::Request)]
enum SupplierEInvoiceDisputeReason: string
{
    case IncorrectVatRate = 'incorrect_vat_rate';
    case IncorrectUnitPrices = 'incorrect_unit_prices';
    case IncorrectBilledQuantity = 'incorrect_billed_quantity';
    case IncorrectBilledItem = 'incorrect_billed_item';
    case DefectiveDeliveredItem = 'defective_delivered_item';
    case DeliveryIssue = 'delivery_issue';
    case BankDetailsError = 'bank_details_error';
    case IncorrectPaymentTerms = 'incorrect_payment_terms';
    case MissingLegalNotice = 'missing_legal_notice';
    case MissingContractualReference = 'missing_contractual_reference';
    case RecipientError = 'recipient_error';
}
