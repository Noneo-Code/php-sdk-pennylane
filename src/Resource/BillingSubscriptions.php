<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\BillingSubscriptionPaymentMethod;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewInvoiceData;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewMode;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\NewRecurringRule;
use Noneo\Sdk\Pennylane\Dto\BillingSubscription\UpdateInvoiceData;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\GetBillingSubscription;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\GetBillingSubscriptionInvoiceLines;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\GetBillingSubscriptionInvoiceLineSections;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\GetBillingSubscriptions;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PostBillingSubscriptions;
use Noneo\Sdk\Pennylane\Requests\BillingSubscriptions\PutBillingSubscriptions;
use Saloon\Http\BaseResource;

final class BillingSubscriptions extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetBillingSubscriptions($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        return new CursorPage(
            array_map(static fn (mixed $item): BillingSubscriptionDto => BillingSubscriptionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, BillingSubscriptionDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetBillingSubscriptions(filters: $filters, sort: $sort), filters: $filters, sort: $sort, perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield BillingSubscriptionDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): BillingSubscriptionDto
    {
        return BillingSubscriptionDto::fromArray($this->connector->send(new GetBillingSubscription($id))->json());
    }

    public function create(
        string $start,
        NewMode $mode,
        PaymentConditions $paymentConditions,
        BillingSubscriptionPaymentMethod $paymentMethod,
        NewRecurringRule $recurringRule,
        int $customerId,
        NewInvoiceData $customerInvoiceData,
        ?string $label = null,
    ): BillingSubscriptionDto {
        $request = new PostBillingSubscriptions(
            start: $start,
            mode: $mode,
            paymentConditions: $paymentConditions,
            paymentMethod: $paymentMethod,
            recurringRule: $recurringRule,
            customerId: $customerId,
            customerInvoiceData: $customerInvoiceData,
            label: $label,
        );

        return BillingSubscriptionDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        ?bool $stop = null,
        ?NewMode $mode = null,
        ?PaymentConditions $paymentConditions = null,
        ?BillingSubscriptionPaymentMethod $paymentMethod = null,
        string|Clear|null $label = null,
        ?NewRecurringRule $recurringRule = null,
        ?int $customerId = null,
        ?UpdateInvoiceData $customerInvoiceData = null,
    ): BillingSubscriptionDto {
        $request = new PutBillingSubscriptions(
            id: $id,
            stop: $stop,
            mode: $mode,
            paymentConditions: $paymentConditions,
            paymentMethod: $paymentMethod,
            label: $label,
            recurringRule: $recurringRule,
            customerId: $customerId,
            customerInvoiceData: $customerInvoiceData,
        );

        return BillingSubscriptionDto::fromArray($this->connector->send($request)->json());
    }

    public function invoiceLineSections(int $billingSubscriptionId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetBillingSubscriptionInvoiceLineSections($billingSubscriptionId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineSectionDto => InvoiceLineSectionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineSectionDto> */
    public function paginateInvoiceLineSections(int $billingSubscriptionId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetBillingSubscriptionInvoiceLineSections($billingSubscriptionId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineSectionDto::fromArray(self::asArray($item));
        }
    }

    public function invoiceLines(int $billingSubscriptionId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetBillingSubscriptionInvoiceLines($billingSubscriptionId, $cursor, $limit, $sort);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        return new CursorPage(
            array_map(static fn (mixed $item): InvoiceLineDto => InvoiceLineDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, InvoiceLineDto> */
    public function paginateInvoiceLines(int $billingSubscriptionId, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetBillingSubscriptionInvoiceLines($billingSubscriptionId, sort: $sort), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield InvoiceLineDto::fromArray(self::asArray($item));
        }
    }
}
