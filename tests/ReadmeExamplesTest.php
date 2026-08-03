<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use Noneo\Sdk\Pennylane\CompanyScope;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Un README qui ment coûte plus cher qu'un README absent : le lecteur copie,
 * colle, et découvre l'erreur en production. Les appels de ressource qu'il
 * montre sont donc confrontés aux signatures réelles.
 */
final class ReadmeExamplesTest extends TestCase
{
    /**
     * @param list<string> $namedArguments
     */
    #[DataProvider('documentedCalls')]
    public function testEveryDocumentedResourceCallMatchesTheRealSignature(
        string $connector,
        string $resource,
        string $method,
        array $namedArguments,
    ): void {
        self::assertTrue(
            method_exists($connector, $resource),
            sprintf('Le README appelle %s::%s(), qui n\'existe pas.', $connector, $resource),
        );

        $returnType = (new \ReflectionMethod($connector, $resource))->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);

        $resourceClass = $returnType->getName();

        self::assertTrue(
            method_exists($resourceClass, $method),
            sprintf('Le README appelle %s::%s(), qui n\'existe pas.', $resourceClass, $method),
        );

        $accepted = array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            (new \ReflectionMethod($resourceClass, $method))->getParameters(),
        );

        foreach ($namedArguments as $argument) {
            self::assertContains(
                $argument,
                $accepted,
                sprintf('Le README passe « %s: » à %s::%s(), qui ne l\'accepte pas.', $argument, $resourceClass, $method),
            );
        }
    }

    /**
     * Extrait les appels `$pennylane->ressource()->methode(…)` et
     * `$firm->company(…)->ressource()->methode(…)` des blocs PHP du README.
     *
     * @return iterable<string, array{string, string, string, list<string>}>
     */
    public static function documentedCalls(): iterable
    {
        $readme = (string) file_get_contents(\dirname(__DIR__).'/README.md');

        $patterns = [
            Pennylane::class => '/\$pennylane->(\w+)\(\)\s*->\s*(\w+)\(([^;]*?)\)\s*;/s',
            CompanyScope::class => '/\$firm->company\([^)]*\)\s*->\s*(\w+)\(\)\s*->\s*(\w+)\(([^;]*?)\)\s*;/s',
        ];

        $seen = 0;

        foreach ($patterns as $connector => $pattern) {
            preg_match_all($pattern, $readme, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                [, $resource, $method, $arguments] = $match;

                // Un nom d'argument est un identifiant collé à « : », suivi
                // d'une espace — ce qui écarte les « https:// » des URL.
                preg_match_all('/(?:^|[,(\s])(\w+):\s/', self::onlyTopLevel($arguments), $named);

                $label = sprintf('%s()->%s() #%d', $resource, $method, ++$seen);

                yield $label => [$connector, $resource, $method, $named[1]];
            }
        }
    }

    /**
     * Neutralise, en les remplaçant par des espaces, les caractères imbriqués
     * dans un appel ou une chaîne littérale : sans quoi le `customerId:` d'un
     * `new EInvoiceImportOptions(customerId: 42)` passé en argument serait pris
     * pour un argument de la méthode appelante.
     */
    private static function onlyTopLevel(string $arguments): string
    {
        $depth = 0;
        $quote = null;
        $result = '';

        foreach (str_split($arguments) as $index => $character) {
            if (null !== $quote) {
                $result .= ' ';

                if ($character === $quote && '\\' !== ($arguments[$index - 1] ?? '')) {
                    $quote = null;
                }

                continue;
            }

            if ("'" === $character || '"' === $character) {
                $quote = $character;
                $result .= ' ';

                continue;
            }

            if ('(' === $character || '[' === $character) {
                ++$depth;
                $result .= ' ';

                continue;
            }

            if (')' === $character || ']' === $character) {
                --$depth;
                $result .= ' ';

                continue;
            }

            $result .= $depth > 0 ? ' ' : $character;
        }

        return $result;
    }

    public function testTheReadmeActuallyContainsDocumentedCalls(): void
    {
        self::assertGreaterThan(5, iterator_count(self::documentedCalls()));
    }
}
