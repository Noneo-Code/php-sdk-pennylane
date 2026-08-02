<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Resource\BankAccounts;
use Noneo\Sdk\Pennylane\Resource\BankEstablishments;
use Noneo\Sdk\Pennylane\Resource\BillingSubscriptions;
use Noneo\Sdk\Pennylane\Resource\Categories;
use Noneo\Sdk\Pennylane\Resource\CategoryGroups;
use Noneo\Sdk\Pennylane\Resource\CommercialDocuments;
use Noneo\Sdk\Pennylane\Resource\CustomerInvoices;
use Noneo\Sdk\Pennylane\Resource\Customers;
use Noneo\Sdk\Pennylane\Resource\Exports;
use Noneo\Sdk\Pennylane\Resource\FileAttachments;
use Noneo\Sdk\Pennylane\Resource\FiscalYears;
use Noneo\Sdk\Pennylane\Resource\GocardlessMandates;
use Noneo\Sdk\Pennylane\Resource\Journals;
use Noneo\Sdk\Pennylane\Resource\LedgerAccounts;
use Noneo\Sdk\Pennylane\Resource\LedgerEntries;
use Noneo\Sdk\Pennylane\Resource\LedgerEntryLines;
use Noneo\Sdk\Pennylane\Resource\Me;
use Noneo\Sdk\Pennylane\Resource\PaRegistrations;
use Noneo\Sdk\Pennylane\Resource\ProAccountMandates;
use Noneo\Sdk\Pennylane\Resource\Products;
use Noneo\Sdk\Pennylane\Resource\PurchaseRequests;
use Noneo\Sdk\Pennylane\Resource\Quotes;
use Noneo\Sdk\Pennylane\Resource\SepaMandates;
use Noneo\Sdk\Pennylane\Resource\SupplierInvoices;
use Noneo\Sdk\Pennylane\Resource\Suppliers;
use Noneo\Sdk\Pennylane\Resource\Transactions;
use Noneo\Sdk\Pennylane\Resource\TrialBalance;
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

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this);
    }

    public function bankEstablishments(): BankEstablishments
    {
        return new BankEstablishments($this);
    }

    public function billingSubscriptions(): BillingSubscriptions
    {
        return new BillingSubscriptions($this);
    }

    public function categories(): Categories
    {
        return new Categories($this);
    }

    public function categoryGroups(): CategoryGroups
    {
        return new CategoryGroups($this);
    }

    public function commercialDocuments(): CommercialDocuments
    {
        return new CommercialDocuments($this);
    }

    public function customers(): Customers
    {
        return new Customers($this);
    }

    public function customerInvoices(): CustomerInvoices
    {
        return new CustomerInvoices($this);
    }

    public function exports(): Exports
    {
        return new Exports($this);
    }

    public function fileAttachments(): FileAttachments
    {
        return new FileAttachments($this);
    }

    public function gocardlessMandates(): GocardlessMandates
    {
        return new GocardlessMandates($this);
    }

    public function journals(): Journals
    {
        return new Journals($this);
    }

    public function ledgerAccounts(): LedgerAccounts
    {
        return new LedgerAccounts($this);
    }

    public function fiscalYears(): FiscalYears
    {
        return new FiscalYears($this);
    }

    public function ledgerEntries(): LedgerEntries
    {
        return new LedgerEntries($this);
    }

    public function ledgerEntryLines(): LedgerEntryLines
    {
        return new LedgerEntryLines($this);
    }

    public function me(): Me
    {
        return new Me($this);
    }

    public function paRegistrations(): PaRegistrations
    {
        return new PaRegistrations($this);
    }

    public function proAccountMandates(): ProAccountMandates
    {
        return new ProAccountMandates($this);
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

    public function sepaMandates(): SepaMandates
    {
        return new SepaMandates($this);
    }

    public function suppliers(): Suppliers
    {
        return new Suppliers($this);
    }

    public function supplierInvoices(): SupplierInvoices
    {
        return new SupplierInvoices($this);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this);
    }

    public function trialBalance(): TrialBalance
    {
        return new TrialBalance($this);
    }
}
