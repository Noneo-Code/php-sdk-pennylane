<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane;

use Noneo\Sdk\Pennylane\Concerns\AppliesPennylaneTimeouts;
use Noneo\Sdk\Pennylane\Concerns\HandlesPennylaneErrors;
use Noneo\Sdk\Pennylane\Concerns\RespectsPennylaneRateLimits;
use Noneo\Sdk\Pennylane\Concerns\SecuresPennylaneTransport;
use Noneo\Sdk\Pennylane\Resource\CategoryGroups;
use Noneo\Sdk\Pennylane\Resource\FileAttachments;
use Noneo\Sdk\Pennylane\Resource\Firm\BankAccounts;
use Noneo\Sdk\Pennylane\Resource\Firm\Categories;
use Noneo\Sdk\Pennylane\Resource\Firm\Changelogs;
use Noneo\Sdk\Pennylane\Resource\Firm\Customers;
use Noneo\Sdk\Pennylane\Resource\Firm\Dms;
use Noneo\Sdk\Pennylane\Resource\Firm\Exports;
use Noneo\Sdk\Pennylane\Resource\Firm\FiscalYears;
use Noneo\Sdk\Pennylane\Resource\Firm\Journals;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerAccounts;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerEntries;
use Noneo\Sdk\Pennylane\Resource\Firm\LedgerEntryLines;
use Noneo\Sdk\Pennylane\Resource\Firm\Suppliers;
use Noneo\Sdk\Pennylane\Resource\Firm\Transactions;
use Noneo\Sdk\Pennylane\Resource\Firm\TrialBalance;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;

/**
 * Décorateur d'entreprise pour la Firm API : préfixe `/companies/{id}` à
 * toutes les requêtes. C'est un `Connector` à part entière (et non un simple
 * relais) car `BaseResource` et `CursorPaginator` exigent tous deux un
 * `Saloon\Http\Connector` concret — hériter permet de réutiliser telles
 * quelles les `Resource\` et `Requests\` déjà écrites pour la Company API.
 */
final class CompanyScope extends Connector
{
    use AppliesPennylaneTimeouts;
    use HandlesPennylaneErrors;
    use RespectsPennylaneRateLimits;
    use SecuresPennylaneTransport;

    /**
     * @see Pennylane::VERSION — même paquet, même version.
     */
    public const string VERSION = Pennylane::VERSION;

    public function __construct(
        private readonly PennylaneFirm $firm,
        private readonly int $companyId,
    ) {
        $authenticator = $this->firm->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $this->authenticate($authenticator);
        }

        $this->useRateLimitStore($this->firm->getRateLimitStore());
        $this->useMaxRetries($this->firm->getMaxRetries());
        $this->useTimeouts($this->firm->getRequestTimeout(), $this->firm->getConnectTimeout());

        $mockClient = $this->firm->getMockClient();

        if ($mockClient instanceof MockClient) {
            $this->withMockClient($mockClient);
        }
    }

    public function resolveBaseUrl(): string
    {
        return rtrim($this->firm->resolveBaseUrl(), '/').'/companies/'.$this->companyId;
    }

    public function journals(): Journals
    {
        return new Journals($this);
    }

    public function ledgerAccounts(): LedgerAccounts
    {
        return new LedgerAccounts($this);
    }

    public function ledgerEntries(): LedgerEntries
    {
        return new LedgerEntries($this);
    }

    public function ledgerEntryLines(): LedgerEntryLines
    {
        return new LedgerEntryLines($this);
    }

    public function categories(): Categories
    {
        return new Categories($this);
    }

    /**
     * Réutilise directement la ressource `Categories` de la Company API :
     * la Firm API expose exactement les mêmes méthodes pour ce groupe
     * (`list`, `get`, `categories`), sans capacité supplémentaire à masquer.
     */
    public function categoryGroups(): CategoryGroups
    {
        return new CategoryGroups($this);
    }

    public function suppliers(): Suppliers
    {
        return new Suppliers($this);
    }

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this);
    }

    /**
     * Réutilise directement la ressource `FileAttachments` de la Company API :
     * elle n'expose qu'un `upload()`, identique côté Firm.
     */
    public function fileAttachments(): FileAttachments
    {
        return new FileAttachments($this);
    }

    public function customers(): Customers
    {
        return new Customers($this);
    }

    public function exports(): Exports
    {
        return new Exports($this);
    }

    public function fiscalYears(): FiscalYears
    {
        return new FiscalYears($this);
    }

    public function trialBalance(): TrialBalance
    {
        return new TrialBalance($this);
    }

    public function changelogs(): Changelogs
    {
        return new Changelogs($this);
    }

    public function dms(): Dms
    {
        return new Dms($this);
    }
}
