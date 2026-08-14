<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

/**
 * Quelles requêtes un appelant peut réellement déclencher depuis un connecteur.
 *
 * Une classe `Request` existe indépendamment de toute ressource : elle peut être
 * écrite, testée, et rester injoignable faute d'une méthode qui l'expose. Comme
 * une même requête sert les deux connecteurs — le scope `/companies/{id}` est
 * porté par `CompanyScope`, pas par la requête —, sa seule existence ne dit rien
 * de sa disponibilité côté cabinet. Ce graphe répond à la question qui compte :
 * en partant de tel connecteur, de ressource en ressource, où peut-on aller ?
 *
 * La lecture est statique, par les `use` : charger les classes exigerait
 * d'instancier des connecteurs authentifiés, et php-cs-fixer garantit qu'aucun
 * import inutilisé ne subsiste.
 */
final class ResourceGraph
{
    private const NAMESPACE_PREFIX = 'Noneo\\Sdk\\Pennylane\\';

    /**
     * @param string       $rootClass nom de classe relatif au namespace racine, ex. `CompanyScope`
     * @param list<string> $barriers  classes à ne pas franchir, mêmes noms relatifs
     *
     * @return list<string> noms courts des classes `Request` atteignables, triés
     */
    public static function requestsReachableFrom(string $srcDir, string $rootClass, array $barriers = []): array
    {
        $queue = [$rootClass];
        $visited = array_fill_keys($barriers, true);
        $requests = [];

        while ([] !== $queue) {
            $class = array_shift($queue);

            if (isset($visited[$class])) {
                continue;
            }

            $visited[$class] = true;

            foreach (self::imports($srcDir, $class) as $import) {
                if (str_starts_with($import, 'Requests\\')) {
                    $requests[self::shortName($import)] = true;

                    continue;
                }

                // Seules les ressources relaient un appel vers une requête ; les
                // DTO, paginateurs et exceptions n'en émettent aucune.
                if (str_starts_with($import, 'Resource\\')) {
                    $queue[] = $import;
                }
            }
        }

        $requests = array_keys($requests);
        sort($requests);

        return $requests;
    }

    /**
     * @return list<string> noms de classes du SDK importées, relatifs au namespace racine
     */
    private static function imports(string $srcDir, string $class): array
    {
        $file = $srcDir.'/'.str_replace('\\', '/', $class).'.php';

        if (!is_file($file)) {
            return [];
        }

        $source = file_get_contents($file);

        if (!is_string($source)) {
            return [];
        }

        $pattern = '/^use '.preg_quote(self::NAMESPACE_PREFIX, '/').'([\w\\\\]+)(?: as \w+)?;/m';

        if (false === preg_match_all($pattern, $source, $matches)) {
            return [];
        }

        return $matches[1];
    }

    private static function shortName(string $class): string
    {
        $position = strrpos($class, '\\');

        return false === $position ? $class : substr($class, $position + 1);
    }
}
