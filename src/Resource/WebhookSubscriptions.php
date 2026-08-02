<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Webhook\CreatedWebhookSubscriptionDto;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookEventType;
use Noneo\Sdk\Pennylane\Dto\Webhook\WebhookSubscriptionDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\DeleteWebhookSubscription;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\GetWebhookSubscription;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\GetWebhookSubscriptions;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\PostWebhookSubscriptions;
use Noneo\Sdk\Pennylane\Requests\WebhookSubscriptions\PutWebhookSubscription;
use Saloon\Http\BaseResource;

final class WebhookSubscriptions extends BaseResource
{
    /** @param list<WebhookEventType> $events */
    public function create(string $callbackUrl, array $events, ?bool $enabled = null): CreatedWebhookSubscriptionDto
    {
        $request = new PostWebhookSubscriptions($callbackUrl, $events, $enabled);

        return CreatedWebhookSubscriptionDto::fromArray($this->connector->send($request)->json());
    }

    public function list(?string $cursor = null, ?int $limit = null): CursorPage
    {
        $rawPage = CursorPage::fromArray($this->connector->send(new GetWebhookSubscriptions($cursor, $limit))->json());

        return new CursorPage(
            array_map(static fn (mixed $item): WebhookSubscriptionDto => WebhookSubscriptionDto::fromArray(self::asArray($item)), $rawPage->items),
            $rawPage->hasMore,
            $rawPage->nextCursor,
        );
    }

    /** @return \Generator<int, WebhookSubscriptionDto> */
    public function paginate(): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetWebhookSubscriptions());

        foreach ($paginator->items() as $item) {
            yield WebhookSubscriptionDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): WebhookSubscriptionDto
    {
        return WebhookSubscriptionDto::fromArray($this->connector->send(new GetWebhookSubscription($id))->json());
    }

    /** @param list<WebhookEventType>|null $events */
    public function update(int $id, ?string $callbackUrl = null, ?array $events = null, ?bool $enabled = null): WebhookSubscriptionDto
    {
        $request = new PutWebhookSubscription($id, $callbackUrl, $events, $enabled);

        return WebhookSubscriptionDto::fromArray($this->connector->send($request)->json());
    }

    public function delete(int $id): void
    {
        $this->connector->send(new DeleteWebhookSubscription($id));
    }

    /** @return mixed[] */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
