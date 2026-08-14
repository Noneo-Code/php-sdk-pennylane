<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Http\Connector\PennylaneConnector;
use Noneo\Sdk\Pennylane\Resource\BankAccounts;
use Noneo\Sdk\Pennylane\Resource\BankEstablishments;
use Noneo\Sdk\Pennylane\Resource\BillingSubscriptions;
use Noneo\Sdk\Pennylane\Resource\Categories;
use Noneo\Sdk\Pennylane\Resource\CategoryGroups;
use Noneo\Sdk\Pennylane\Resource\Changelogs;
use Noneo\Sdk\Pennylane\Resource\CommercialDocuments;
use Noneo\Sdk\Pennylane\Resource\CustomerInvoices;
use Noneo\Sdk\Pennylane\Resource\Customers;
use Noneo\Sdk\Pennylane\Resource\EInvoices;
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
use Noneo\Sdk\Pennylane\Resource\WebhookSubscriptions;
use Psr\SimpleCache\CacheInterface;

/**
 * Client de la Company API v2. Façade sans héritage Saloon : le transport est
 * un détail d'implémentation, remplaçable sans toucher au contrat public.
 */
final class Pennylane
{
    /**
     * Version du paquet, telle qu'annoncée dans le `User-Agent`. À faire
     * évoluer avec le CHANGELOG à chaque release — un test le vérifie.
     */
    public const string VERSION = '0.1.0';

    public const int DEFAULT_MAX_RETRIES = PennylaneConnector::DEFAULT_MAX_RETRIES;

    private readonly PennylaneConnector $connector;

    /**
     * `$apiToken` est marqué sensible : une trace conservant les arguments
     * afficherait le jeton en clair.
     */
    public function __construct(
        #[\SensitiveParameter] string $apiToken,
        ?CacheInterface $rateLimitCache = null,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?float $requestTimeout = null,
        ?float $connectTimeout = null,
    ) {
        $this->connector = new PennylaneConnector(
            $apiToken,
            $rateLimitCache,
            $maxRetries,
            $requestTimeout,
            $connectTimeout,
        );
    }

    /**
     * Accès au transport Saloon sous-jacent, hors contrat de compatibilité.
     *
     * @internal réservé à `Testing\` et aux tests du SDK
     */
    public function connector(): PennylaneConnector
    {
        return $this->connector;
    }

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this->connector);
    }

    public function bankEstablishments(): BankEstablishments
    {
        return new BankEstablishments($this->connector);
    }

    public function billingSubscriptions(): BillingSubscriptions
    {
        return new BillingSubscriptions($this->connector);
    }

    public function categories(): Categories
    {
        return new Categories($this->connector);
    }

    public function categoryGroups(): CategoryGroups
    {
        return new CategoryGroups($this->connector);
    }

    public function changelogs(): Changelogs
    {
        return new Changelogs($this->connector);
    }

    public function commercialDocuments(): CommercialDocuments
    {
        return new CommercialDocuments($this->connector);
    }

    public function customers(): Customers
    {
        return new Customers($this->connector);
    }

    public function customerInvoices(): CustomerInvoices
    {
        return new CustomerInvoices($this->connector);
    }

    public function eInvoices(): EInvoices
    {
        return new EInvoices($this->connector);
    }

    public function exports(): Exports
    {
        return new Exports($this->connector);
    }

    public function fileAttachments(): FileAttachments
    {
        return new FileAttachments($this->connector);
    }

    public function gocardlessMandates(): GocardlessMandates
    {
        return new GocardlessMandates($this->connector);
    }

    public function journals(): Journals
    {
        return new Journals($this->connector);
    }

    public function ledgerAccounts(): LedgerAccounts
    {
        return new LedgerAccounts($this->connector);
    }

    public function fiscalYears(): FiscalYears
    {
        return new FiscalYears($this->connector);
    }

    public function ledgerEntries(): LedgerEntries
    {
        return new LedgerEntries($this->connector);
    }

    public function ledgerEntryLines(): LedgerEntryLines
    {
        return new LedgerEntryLines($this->connector);
    }

    public function me(): Me
    {
        return new Me($this->connector);
    }

    public function paRegistrations(): PaRegistrations
    {
        return new PaRegistrations($this->connector);
    }

    public function proAccountMandates(): ProAccountMandates
    {
        return new ProAccountMandates($this->connector);
    }

    public function products(): Products
    {
        return new Products($this->connector);
    }

    public function purchaseRequests(): PurchaseRequests
    {
        return new PurchaseRequests($this->connector);
    }

    public function quotes(): Quotes
    {
        return new Quotes($this->connector);
    }

    public function sepaMandates(): SepaMandates
    {
        return new SepaMandates($this->connector);
    }

    public function suppliers(): Suppliers
    {
        return new Suppliers($this->connector);
    }

    public function supplierInvoices(): SupplierInvoices
    {
        return new SupplierInvoices($this->connector);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this->connector);
    }

    public function trialBalance(): TrialBalance
    {
        return new TrialBalance($this->connector);
    }

    public function webhookSubscriptions(): WebhookSubscriptions
    {
        return new WebhookSubscriptions($this->connector);
    }
}
