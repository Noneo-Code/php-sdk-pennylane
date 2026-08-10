<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Concerns;

use Noneo\Sdk\Pennylane\Http\FloatGuard;
use Saloon\Http\PendingRequest;
use Saloon\Repositories\Body\ArrayBodyRepository;

/**
 * Dernier filtre avant l'envoi : aucun `float` ne quitte le SDK, ni dans le
 * corps JSON, ni dans la query.
 *
 * Le contrôle vit ici plutôt que dans les quelque 180 classes de requête :
 * c'est le seul point par lequel tout passe, y compris les `defaultBody()` qui
 * construisent leur tableau à la main sans passer par `Payload::build()`. Le
 * `Payload` garde le sien pour lever plus tôt, sur la ligne d'appel ; celui-ci
 * est le filet qui ne peut pas être contourné.
 *
 * Le pourquoi est dans {@see FloatGuard} : un `float` ne représente pas
 * exactement un montant décimal, et une écriture comptable fausse d'un centime
 * ne se voit pas.
 */
trait RejectsFloatValues
{
    /**
     * Le contrôle passe par un middleware plutôt que par le corps du plugin :
     * au moment où Saloon boote les plugins, ni la query ni le corps ne sont
     * encore fusionnés dans la `PendingRequest`. Le pipeline de requête, lui,
     * s'exécute après.
     */
    public function bootRejectsFloatValues(PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onRequest(
            static function (PendingRequest $pendingRequest): void {
                FloatGuard::assertNoFloat($pendingRequest->query()->all());

                $body = $pendingRequest->body();

                if ($body instanceof ArrayBodyRepository) {
                    FloatGuard::assertNoFloat($body->all());
                }
            },
            'rejectsFloatValues',
        );
    }
}
