<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceDisputeReason;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceRefusalReason;
use PHPUnit\Framework\TestCase;

/**
 * La spec définit deux jeux de raisons disjoints selon la transition visée :
 * les confondre laisse passer des combinaisons que l'API rejette en 422.
 */
final class SupplierEInvoiceReasonTest extends TestCase
{
    public function testDisputeReasonsMatchTheDocumentedSet(): void
    {
        self::assertSame(
            [
                'incorrect_vat_rate',
                'incorrect_unit_prices',
                'incorrect_billed_quantity',
                'incorrect_billed_item',
                'defective_delivered_item',
                'delivery_issue',
                'bank_details_error',
                'incorrect_payment_terms',
                'missing_legal_notice',
                'missing_contractual_reference',
                'recipient_error',
            ],
            array_column(SupplierEInvoiceDisputeReason::cases(), 'value'),
        );
    }

    public function testRefusalReasonsMatchTheDocumentedSet(): void
    {
        self::assertSame(
            [
                'incorrect_vat_rate',
                'contract_completed',
                'duplicate_invoice',
                'recipient_error',
                'incorrect_prices',
                'non_compliant_invoice',
            ],
            array_column(SupplierEInvoiceRefusalReason::cases(), 'value'),
        );
    }

    public function testRefusalOnlyReasonsAreNotOfferedForADispute(): void
    {
        $disputeReasons = array_column(SupplierEInvoiceDisputeReason::cases(), 'value');

        foreach (['contract_completed', 'duplicate_invoice', 'incorrect_prices', 'non_compliant_invoice'] as $refusalOnly) {
            self::assertNotContains($refusalOnly, $disputeReasons);
        }
    }

    public function testDisputeOnlyReasonsAreNotOfferedForARefusal(): void
    {
        $refusalReasons = array_column(SupplierEInvoiceRefusalReason::cases(), 'value');

        foreach (['delivery_issue', 'defective_delivered_item', 'missing_legal_notice'] as $disputeOnly) {
            self::assertNotContains($disputeOnly, $refusalReasons);
        }
    }
}
