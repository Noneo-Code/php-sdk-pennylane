<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionStatus;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\RecurringRuleType;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class BillingSubscriptionDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getBillingSubscription')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'next_occurrence' => '2023-01-01',
            'prev_occurrence' => '2023-01-01',
            'stopped_at' => null,
            'start' => '2023-01-01',
            'finish' => '2023-12-31',
            'status' => 'in_progress',
            'mode' => 'email',
            'activated_at' => '2023-08-30T10:08:08.146343Z',
            'payment_conditions' => 'upon_receipt',
            'payment_method' => 'gocardless_direct_debit',
            'label' => 'Demo label',
            'email_settings' => [
                'recipients' => ['customer@example.com'],
                'billing_email_template' => ['id' => 1, 'label' => 'Template'],
            ],
            'recurring_rule' => [
                'day_of_month' => [1],
                'month_of_year' => null,
                'week_start' => null,
                'day' => null,
                'rule_type' => 'monthly',
                'interval' => 1,
                'count' => 12,
                'until' => null,
            ],
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'customer_invoice_data' => [
                'label' => 'Demo label',
                'currency' => 'EUR',
                'amount' => '230.32',
                'currency_amount' => '230.32',
                'currency_amount_before_tax' => '196.32',
                'exchange_rate' => '1.0',
                'currency_tax' => '34.0',
                'language' => 'fr_FR',
                'customer_invoice_template' => ['id' => 42],
                'discount' => ['type' => 'absolute', 'value' => '25'],
                'pdf_invoice_free_text' => 'Thanks for paying this invoice',
                'pdf_invoice_subject' => 'Invoice subject',
                'pdf_description' => 'Invoice description',
                'special_mention' => 'Additional details',
                'invoice_line_sections' => ['url' => 'https://app.pennylane.com/api/external/v2/billing_subscriptions/42/invoice_line_sections'],
                'invoice_lines' => ['url' => 'https://app.pennylane.com/api/external/v2/billing_subscriptions/42/invoice_lines'],
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $subscription = BillingSubscriptionDto::fromArray(self::documentedPayload());

        self::assertSame(42, $subscription->id);
        self::assertSame('2023-01-01', $subscription->nextOccurrence);
        self::assertSame('2023-01-01', $subscription->prevOccurrence);
        self::assertNull($subscription->stoppedAt);
        self::assertSame('2023-01-01', $subscription->start);
        self::assertSame('2023-12-31', $subscription->finish);
        self::assertSame(BillingSubscriptionStatus::InProgress, $subscription->status);
        self::assertSame(BillingSubscriptionMode::Email, $subscription->mode);
        self::assertSame(PaymentConditions::UponReceipt, $subscription->paymentConditions);
        self::assertSame(BillingSubscriptionPaymentMethod::GocardlessDirectDebit, $subscription->paymentMethod);
        self::assertSame('Demo label', $subscription->label);
        self::assertNotNull($subscription->emailSettings);
        self::assertSame(['customer@example.com'], $subscription->emailSettings->recipients);
        self::assertSame(1, $subscription->emailSettings->billingEmailTemplateId);
        self::assertSame('Template', $subscription->emailSettings->billingEmailTemplateLabel);
        self::assertSame([1], $subscription->recurringRule->dayOfMonth);
        self::assertSame(RecurringRuleType::Monthly, $subscription->recurringRule->ruleType);
        self::assertSame(1, $subscription->recurringRule->interval);
        self::assertSame(12, $subscription->recurringRule->count);
        self::assertSame(42, $subscription->customerId);
        self::assertSame('https://app.pennylane.com/api/external/v2/customers/42', $subscription->customerUrl);
        self::assertSame('230.32', $subscription->invoiceData->amount);
        self::assertSame(42, $subscription->invoiceData->customerInvoiceTemplateId);
        self::assertSame('2023-08-30T10:08:08.146343Z', $subscription->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $subscription->updatedAt);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['payment_conditions'] = null;
        $payload['label'] = null;
        $payload['email_settings'] = null;
        $payload['customer'] = null;

        $subscription = BillingSubscriptionDto::fromArray($payload);

        self::assertNull($subscription->paymentConditions);
        self::assertNull($subscription->label);
        self::assertNull($subscription->emailSettings);
        self::assertNull($subscription->customerId);
        self::assertNull($subscription->customerUrl);
    }

    public function testItRejectsAMissingId(): void
    {
        $payload = self::documentedPayload();
        unset($payload['id']);

        $this->expectException(\InvalidArgumentException::class);

        BillingSubscriptionDto::fromArray($payload);
    }
}
