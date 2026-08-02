<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Transaction;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;

final readonly class TransactionDto
{
    /** @param list<CategoryDto> $categories */
    public function __construct(
        public int $id,
        public ?string $label,
        public bool $attachmentRequired,
        public string $date,
        public ?string $outstandingBalance,
        public string $createdAt,
        public string $updatedAt,
        public ?string $archivedAt,
        public ?Currency $currency,
        public string $currencyAmount,
        public string $amount,
        public ?string $currencyFee,
        public ?string $fee,
        public int $journalId,
        public string $journalUrl,
        public int $bankAccountId,
        public string $bankAccountUrl,
        public ?int $customerId,
        public ?string $customerUrl,
        public ?int $supplierId,
        public ?string $supplierUrl,
        public array $categories,
        public string $matchedInvoicesUrl,
        public ?string $interbankCode,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $attachmentRequired = $body['attachment_required'] ?? null;
        $date = $body['date'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $currency = $body['currency'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $amount = $body['amount'] ?? null;
        $journal = $body['journal'] ?? null;
        $bankAccount = $body['bank_account'] ?? null;
        $customer = $body['customer'] ?? null;
        $supplier = $body['supplier'] ?? null;
        $categories = $body['categories'] ?? null;
        $matchedInvoices = $body['matched_invoices'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_bool($attachmentRequired)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir un booléen "attachment_required".');
        }

        if (!is_string($date)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir une "date" textuelle.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        if (null !== $currency && !is_string($currency)) {
            throw new \InvalidArgumentException('La "currency" de la transaction Pennylane doit être textuelle ou nulle.');
        }

        if (!is_string($currencyAmount) || !is_string($amount)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir "currency_amount" et "amount".');
        }

        if (!is_array($journal) || !is_int($journal['id'] ?? null) || !is_string($journal['url'] ?? null)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir un "journal" avec un "id" et une "url".');
        }

        if (!is_array($bankAccount) || !is_int($bankAccount['id'] ?? null) || !is_string($bankAccount['url'] ?? null)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir un "bank_account" avec un "id" et une "url".');
        }

        if (null !== $customer && !is_array($customer)) {
            throw new \InvalidArgumentException('Le "customer" de la transaction Pennylane doit être un objet ou nul.');
        }

        if (null !== $supplier && !is_array($supplier)) {
            throw new \InvalidArgumentException('Le "supplier" de la transaction Pennylane doit être un objet ou nul.');
        }

        if (!is_array($categories)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir un tableau "categories".');
        }

        if (!is_array($matchedInvoices) || !is_string($matchedInvoices['url'] ?? null)) {
            throw new \InvalidArgumentException('La transaction de la réponse Pennylane doit contenir un "matched_invoices" avec une "url".');
        }

        $mappedCategories = [];

        foreach ($categories as $category) {
            if (!is_array($category)) {
                throw new \InvalidArgumentException('Les "categories" de la transaction Pennylane doivent être des objets.');
            }

            $mappedCategories[] = CategoryDto::fromArray($category);
        }

        return new self(
            id: $id,
            label: is_string($body['label'] ?? null) ? $body['label'] : null,
            attachmentRequired: $attachmentRequired,
            date: $date,
            outstandingBalance: is_string($body['outstanding_balance'] ?? null) ? $body['outstanding_balance'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            archivedAt: is_string($body['archived_at'] ?? null) ? $body['archived_at'] : null,
            currency: null !== $currency ? Currency::from($currency) : null,
            currencyAmount: $currencyAmount,
            amount: $amount,
            currencyFee: is_string($body['currency_fee'] ?? null) ? $body['currency_fee'] : null,
            fee: is_string($body['fee'] ?? null) ? $body['fee'] : null,
            journalId: $journal['id'],
            journalUrl: $journal['url'],
            bankAccountId: $bankAccount['id'],
            bankAccountUrl: $bankAccount['url'],
            customerId: is_array($customer) && is_int($customer['id'] ?? null) ? $customer['id'] : null,
            customerUrl: is_array($customer) && is_string($customer['url'] ?? null) ? $customer['url'] : null,
            supplierId: is_array($supplier) && is_int($supplier['id'] ?? null) ? $supplier['id'] : null,
            supplierUrl: is_array($supplier) && is_string($supplier['url'] ?? null) ? $supplier['url'] : null,
            categories: $mappedCategories,
            matchedInvoicesUrl: $matchedInvoices['url'],
            interbankCode: is_string($body['interbank_code'] ?? null) ? $body['interbank_code'] : null,
        );
    }
}
