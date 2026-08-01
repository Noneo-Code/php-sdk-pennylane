<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Resource\Categories;
use Noneo\Sdk\Pennylane\Resource\CategoryGroups;
use Noneo\Sdk\Pennylane\Resource\CustomerInvoices;
use Noneo\Sdk\Pennylane\Resource\Customers;
use Noneo\Sdk\Pennylane\Resource\PaRegistrations;
use Noneo\Sdk\Pennylane\Resource\Products;
use Noneo\Sdk\Pennylane\Resource\PurchaseRequests;
use Noneo\Sdk\Pennylane\Resource\Quotes;
use Noneo\Sdk\Pennylane\Resource\SupplierInvoices;
use Noneo\Sdk\Pennylane\Resource\Suppliers;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

final class Pennylane extends Connector
{
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;

    public function __construct(Authenticator $authenticator, ?RateLimitStore $rateLimitStore = null)
    {
        $this->authenticate($authenticator);

        if ($rateLimitStore instanceof RateLimitStore) {
            $this->useRateLimitStore($rateLimitStore);
        }
    }

    public function resolveBaseUrl(): string
    {
        return 'https://app.pennylane.com/api/external/v2';
    }

    public function categories(): Categories
    {
        return new Categories($this);
    }

    public function categoryGroups(): CategoryGroups
    {
        return new CategoryGroups($this);
    }

    public function customers(): Customers
    {
        return new Customers($this);
    }

    public function customerInvoices(): CustomerInvoices
    {
        return new CustomerInvoices($this);
    }

    public function paRegistrations(): PaRegistrations
    {
        return new PaRegistrations($this);
    }

    public function products(): Products
    {
        return new Products($this);
    }

    public function purchaseRequests(): PurchaseRequests
    {
        return new PurchaseRequests($this);
    }

    public function quotes(): Quotes
    {
        return new Quotes($this);
    }

    public function suppliers(): Suppliers
    {
        return new Suppliers($this);
    }

    public function supplierInvoices(): SupplierInvoices
    {
        return new SupplierInvoices($this);
    }
}
