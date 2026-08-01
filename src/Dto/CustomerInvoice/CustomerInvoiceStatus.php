<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

enum CustomerInvoiceStatus: string
{
    case Archived = 'archived';
    case Incomplete = 'incomplete';
    case Cancelled = 'cancelled';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case PartiallyCancelled = 'partially_cancelled';
    case Upcoming = 'upcoming';
    case Late = 'late';
    case Draft = 'draft';
    case CreditNote = 'credit_note';
    case Proforma = 'proforma';
    case ShippingOrder = 'shipping_order';
    case PurchasingOrder = 'purchasing_order';
    case EstimatePending = 'estimate_pending';
    case EstimateAccepted = 'estimate_accepted';
    case EstimateInvoiced = 'estimate_invoiced';
    case EstimateDenied = 'estimate_denied';
}
