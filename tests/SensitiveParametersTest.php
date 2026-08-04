<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Garde-fou de recensement : sans `#[\SensitiveParameter]`, un secret passé en
 * argument apparaît en clair dans toute trace qui conserve ses arguments — et
 * donc dans Sentry, Bugsink et les journaux applicatifs. Plutôt que de figer
 * une liste qui se périmerait au premier ajout, ce test relit `src/` et impose
 * l'attribut sur tout paramètre dont le nom annonce un secret.
 */
final class SensitiveParametersTest extends TestCase
{
    /**
     * Un `Authenticator` Saloon porte le jeton dans une propriété publique :
     * le passer en argument revient à passer le jeton lui-même.
     */
    private const SECRET_NAME_PATTERN = '/secret|token|password|passphrase|credential|authenticator/i';

    public function testEverySecretBearingParameterIsMarkedSensitive(): void
    {
        $offenders = [];

        foreach ($this->sdkMethods() as [$class, $method]) {
            foreach ($method->getParameters() as $parameter) {
                if (1 !== preg_match(self::SECRET_NAME_PATTERN, $parameter->getName())) {
                    continue;
                }

                if ([] === $parameter->getAttributes(\SensitiveParameter::class)) {
                    $offenders[] = sprintf('%s::%s($%s)', $class, $method->getName(), $parameter->getName());
                }
            }
        }

        self::assertSame([], $offenders, sprintf(
            "Ces paramètres portent un secret sans #[\\SensitiveParameter] :\n- %s",
            implode("\n- ", $offenders),
        ));
    }

    /**
     * Le test précédent ne vaut que s'il inspecte réellement le SDK : sans ce
     * plancher, une erreur de chargement le rendrait vert et muet.
     */
    public function testTheScanActuallyReachesTheSdk(): void
    {
        self::assertGreaterThan(500, iterator_count($this->sdkMethods()));
    }

    /**
     * @return \Generator<int, array{class-string, \ReflectionMethod}>
     */
    private function sdkMethods(): \Generator
    {
        $src = realpath(__DIR__.'/../src');
        self::assertIsString($src);

        /** @var iterable<\SplFileInfo> $files */
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src));

        foreach ($files as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($src) + 1, -4);
            /** @var class-string $class */
            $class = 'Noneo\\Sdk\\Pennylane\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                continue;
            }

            foreach ((new \ReflectionClass($class))->getMethods() as $method) {
                // Une méthode issue d'un trait Saloon (`AuthorizationCodeGrant`
                // et ses jetons de rafraîchissement) se déclare dans la classe
                // utilisatrice mais vit dans `vendor/` : nous ne pouvons pas
                // l'annoter, et l'exiger rendrait le test impossible à tenir.
                if (str_starts_with((string) $method->getFileName(), $src)) {
                    yield [$class, $method];
                }
            }
        }
    }
}
