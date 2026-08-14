<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Http\Connector\CompanyScopeConnector;
use Noneo\Sdk\Pennylane\Http\Connector\FirmConnector;
use Noneo\Sdk\Pennylane\Resource\CategoryGroups;
use Noneo\Sdk\Pennylane\Resource\FileAttachments;
use Noneo\Sdk\Pennylane\Resource\Firm\BankAccounts;
use Noneo\Sdk\Pennylane\Resource\Firm\Categories;
use Noneo\Sdk\Pennylane\Resource\Firm\Changelogs;
use Noneo\Sdk\Pennylane\Resource\Firm\CustomerInvoices;
use Noneo\Sdk\Pennylane\Resource\Firm\Customers;
use Noneo\Sdk\Pennylane\Resource\Firm\Dms;
use Noneo\Sdk\Pennylane\Resource\Firm\Exports;
use Noneo\Sdk\Pennylane\Resource\Firm\FiscalYears;
use Noneo\Sdk\Pennylane\Resource\Firm\Journals;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerAccounts;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerEntries;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerEntryLines;
use Noneo\Sdk\Pennylane\Resource\Firm\SupplierInvoices;
use Noneo\Sdk\Pennylane\Resource\Firm\Suppliers;
use Noneo\Sdk\Pennylane\Resource\Firm\Transactions;
use Noneo\Sdk\Pennylane\Resource\Firm\TrialBalance;

/**
 * Vue d'entreprise sur la Firm API : toutes les requêtes sont préfixées
 * `/companies/{id}`. Façade sans héritage Saloon, obtenue via
 * {@see PennylaneFirm::company()}.
 */
final class CompanyScope
{
    /**
     * @see Pennylane::VERSION — même paquet, même version.
     */
    public const string VERSION = Pennylane::VERSION;

    private readonly CompanyScopeConnector $connector;

    public function __construct(FirmConnector $firm, int $companyId)
    {
        $this->connector = new CompanyScopeConnector($firm, $companyId);
    }

    /**
     * Accès au transport Saloon sous-jacent, hors contrat de compatibilité.
     *
     * @internal réservé à `Testing\` et aux tests du SDK
     */
    public function connector(): CompanyScopeConnector
    {
        return $this->connector;
    }

    public function journals(): Journals
    {
        return new Journals($this->connector);
    }

    public function ledgerAccounts(): LedgerAccounts
    {
        return new LedgerAccounts($this->connector);
    }

    public function ledgerEntries(): LedgerEntries
    {
        return new LedgerEntries($this->connector);
    }

    public function ledgerEntryLines(): LedgerEntryLines
    {
        return new LedgerEntryLines($this->connector);
    }

    public function categories(): Categories
    {
        return new Categories($this->connector);
    }

    /**
     * Réutilise directement la ressource `CategoryGroups` de la Company API :
     * la Firm API expose exactement les mêmes méthodes pour ce groupe
     * (`list`, `get`, `categories`), sans capacité supplémentaire à masquer.
     */
    public function categoryGroups(): CategoryGroups
    {
        return new CategoryGroups($this->connector);
    }

    public function suppliers(): Suppliers
    {
        return new Suppliers($this->connector);
    }

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this->connector);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this->connector);
    }

    /**
     * Réutilise directement la ressource `FileAttachments` de la Company API :
     * elle n'expose qu'un `upload()`, identique côté Firm.
     */
    public function fileAttachments(): FileAttachments
    {
        return new FileAttachments($this->connector);
    }

    public function customers(): Customers
    {
        return new Customers($this->connector);
    }

    public function supplierInvoices(): SupplierInvoices
    {
        return new SupplierInvoices($this->connector);
    }

    public function customerInvoices(): CustomerInvoices
    {
        return new CustomerInvoices($this->connector);
    }

    public function exports(): Exports
    {
        return new Exports($this->connector);
    }

    public function fiscalYears(): FiscalYears
    {
        return new FiscalYears($this->connector);
    }

    public function trialBalance(): TrialBalance
    {
        return new TrialBalance($this->connector);
    }

    public function changelogs(): Changelogs
    {
        return new Changelogs($this->connector);
    }

    public function dms(): Dms
    {
        return new Dms($this->connector);
    }
}
