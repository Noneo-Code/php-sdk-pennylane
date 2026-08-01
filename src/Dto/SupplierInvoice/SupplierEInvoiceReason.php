<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\SupplierInvoice;

enum SupplierEInvoiceReason: string
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
    case ContractCompleted = 'contract_completed';
    case DuplicateInvoice = 'duplicate_invoice';
    case IncorrectPrices = 'incorrect_prices';
    case NonCompliantInvoice = 'non_compliant_invoice';
}
