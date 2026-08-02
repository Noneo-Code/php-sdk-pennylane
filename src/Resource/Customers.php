<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\Customer\BillingLanguage;
use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Dto\Customer\CompanyCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\ContactDto;
use Noneo\Sdk\Pennylane\Dto\Customer\CustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\IndividualCustomerDto;
use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCompanyCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomerCategories;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomerContacts;
use Noneo\Sdk\Pennylane\Requests\Customers\GetCustomers;
use Noneo\Sdk\Pennylane\Requests\Customers\GetIndividualCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PostCompanyCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PostIndividualCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PutCompanyCustomer;
use Noneo\Sdk\Pennylane\Requests\Customers\PutCustomerCategories;
use Noneo\Sdk\Pennylane\Requests\Customers\PutIndividualCustomer;
use Saloon\Http\BaseResource;

final class Customers extends BaseResource
{
    use DecodesPennylanePayloads;

    public function getCompany(int $id): CompanyCustomerDto
    {
        return CompanyCustomerDto::fromArray($this->connector->send(new GetCompanyCustomer($id))->json());
    }

    /** @param string[]|null $emails */
    public function createCompany(
        string $name,
        Address $billingAddress,
        ?string $vatNumber = null,
        ?string $regNo = null,
        ?string $ledgerAccountNumber = null,
        ?string $phone = null,
        ?Address $deliveryAddress = null,
        ?PaymentConditions $paymentConditions = null,
        ?string $billingIban = null,
        ?string $recipient = null,
        ?string $reference = null,
        ?string $notes = null,
        ?array $emails = null,
        ?string $externalReference = null,
        ?BillingLanguage $billingLanguage = null,
    ): CompanyCustomerDto {
        $request = new PostCompanyCustomer(
            name: $name,
            billingAddress: $billingAddress,
            vatNumber: $vatNumber,
            regNo: $regNo,
            ledgerAccountNumber: $ledgerAccountNumber,
            phone: $phone,
            deliveryAddress: $deliveryAddress,
            paymentConditions: $paymentConditions,
            billingIban: $billingIban,
            recipient: $recipient,
            reference: $reference,
            notes: $notes,
            emails: $emails,
            externalReference: $externalReference,
            billingLanguage: $billingLanguage,
        );

        return CompanyCustomerDto::fromArray($this->connector->send($request)->json());
    }

    /** @param string[]|null $emails */
    public function updateCompany(
        int $id,
        string|Clear|null $name = null,
        string|Clear|null $vatNumber = null,
        string|Clear|null $regNo = null,
        string|Clear|null $phone = null,
        Address|Clear|null $billingAddress = null,
        Address|Clear|null $deliveryAddress = null,
        PaymentConditions|Clear|null $paymentConditions = null,
        string|Clear|null $billingIban = null,
        string|Clear|null $recipient = null,
        string|Clear|null $reference = null,
        string|Clear|null $notes = null,
        array|Clear|null $emails = null,
        string|Clear|null $externalReference = null,
        BillingLanguage|Clear|null $billingLanguage = null,
    ): CompanyCustomerDto {
        $request = new PutCompanyCustomer(
            id: $id,
            name: $name,
            vatNumber: $vatNumber,
            regNo: $regNo,
            phone: $phone,
            billingAddress: $billingAddress,
            deliveryAddress: $deliveryAddress,
            paymentConditions: $paymentConditions,
            billingIban: $billingIban,
            recipient: $recipient,
            reference: $reference,
            notes: $notes,
            emails: $emails,
            externalReference: $externalReference,
            billingLanguage: $billingLanguage,
        );

        return CompanyCustomerDto::fromArray($this->connector->send($request)->json());
    }

    public function getIndividual(int $id): IndividualCustomerDto
    {
        return IndividualCustomerDto::fromArray($this->connector->send(new GetIndividualCustomer($id))->json());
    }

    /** @param string[]|null $emails */
    public function createIndividual(
        string $firstName,
        string $lastName,
        Address $billingAddress,
        ?string $ledgerAccountNumber = null,
        ?string $phone = null,
        ?Address $deliveryAddress = null,
        ?PaymentConditions $paymentConditions = null,
        ?string $billingIban = null,
        ?string $recipient = null,
        ?string $reference = null,
        ?string $notes = null,
        ?array $emails = null,
        ?string $externalReference = null,
        ?BillingLanguage $billingLanguage = null,
    ): IndividualCustomerDto {
        $request = new PostIndividualCustomer(
            firstName: $firstName,
            lastName: $lastName,
            billingAddress: $billingAddress,
            ledgerAccountNumber: $ledgerAccountNumber,
            phone: $phone,
            deliveryAddress: $deliveryAddress,
            paymentConditions: $paymentConditions,
            billingIban: $billingIban,
            recipient: $recipient,
            reference: $reference,
            notes: $notes,
            emails: $emails,
            externalReference: $externalReference,
            billingLanguage: $billingLanguage,
        );

        return IndividualCustomerDto::fromArray($this->connector->send($request)->json());
    }

    /** @param string[]|null $emails */
    public function updateIndividual(
        int $id,
        string|Clear|null $firstName = null,
        string|Clear|null $lastName = null,
        string|Clear|null $phone = null,
        Address|Clear|null $billingAddress = null,
        Address|Clear|null $deliveryAddress = null,
        PaymentConditions|Clear|null $paymentConditions = null,
        string|Clear|null $billingIban = null,
        string|Clear|null $recipient = null,
        string|Clear|null $reference = null,
        string|Clear|null $notes = null,
        array|Clear|null $emails = null,
        string|Clear|null $externalReference = null,
        BillingLanguage|Clear|null $billingLanguage = null,
    ): IndividualCustomerDto {
        $request = new PutIndividualCustomer(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            phone: $phone,
            billingAddress: $billingAddress,
            deliveryAddress: $deliveryAddress,
            paymentConditions: $paymentConditions,
            billingIban: $billingIban,
            recipient: $recipient,
            reference: $reference,
            notes: $notes,
            emails: $emails,
            externalReference: $externalReference,
            billingLanguage: $billingLanguage,
        );

        return IndividualCustomerDto::fromArray($this->connector->send($request)->json());
    }

    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetCustomers($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = self::customerFromArray($item);
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CustomerDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetCustomers(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
            perPage: $perPage,
        );

        foreach ($paginator->items() as $item) {
            yield self::customerFromArray($item);
        }
    }

    public function get(int $id): CustomerDto
    {
        return self::customerFromArray($this->connector->send(new GetCustomer($id))->json());
    }

    public function contacts(int $customerId, ?string $cursor = null, ?int $limit = null, ?Sort $sort = null): CursorPage
    {
        $request = new GetCustomerContacts($customerId, $cursor, $limit, $sort instanceof Sort ? (string) $sort : null);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = ContactDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, ContactDto> */
    public function paginateContacts(int $customerId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerContacts($customerId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield ContactDto::fromArray(self::asArray($item));
        }
    }

    public function categories(int $customerId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetCustomerCategories($customerId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $customerId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCustomerCategories($customerId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    /**
     * @param list<CategoryAllocation> $allocations
     *
     * @return CategoryDto[]
     */
    public function categorize(int $customerId, array $allocations): array
    {
        $response = $this->connector->send(new PutCustomerCategories($customerId, $allocations));

        $categories = [];
        foreach (self::asArray($response->json()) as $item) {
            $categories[] = CategoryDto::fromArray(self::asArray($item));
        }

        return $categories;
    }

    private static function customerFromArray(mixed $item): CustomerDto
    {
        $item = self::asArray($item);
        $type = $item['customer_type'] ?? null;

        return match ($type) {
            'company' => CompanyCustomerDto::fromArray($item),
            'individual' => IndividualCustomerDto::fromArray($item),
            default => throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "customer_type" valant "company" ou "individual".'),
        };
    }
}
