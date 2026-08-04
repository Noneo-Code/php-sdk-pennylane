<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use PhpCsFixer\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Le paquet ne se juge pas qu'à son code : une archive Composer qui embarque
 * 9 Mo de specs, un `license: MIT` sans texte de licence ou un exemple qui ne
 * compile plus sont des régressions produit. Elles se testent comme le reste.
 */
final class PackagingTest extends TestCase
{
    private const EXAMPLES = [
        'quickstart.php',
        'invoicing_lifecycle.php',
        'firm_portfolio.php',
        'webhook_receiver.php',
        'einvoice_import.php',
    ];

    public function testLicenseFileCarriesTheMitTextDeclaredByComposer(): void
    {
        $license = self::read('LICENSE');

        self::assertStringContainsString('MIT License', $license);
        self::assertStringContainsString('Noneo', $license);
        self::assertStringContainsString('Permission is hereby granted, free of charge', $license);
        self::assertStringContainsString('WITHOUT WARRANTY OF ANY KIND', $license);
    }

    public function testChangelogFollowsKeepAChangelogAndDocumentsTheFirstVersion(): void
    {
        $changelog = self::read('CHANGELOG.md');

        self::assertStringContainsString('Keep a Changelog', $changelog);
        self::assertStringContainsString('Semantic Versioning', $changelog);
        self::assertStringContainsString('## [0.1.0]', $changelog);
    }

    /**
     * Un `git archive` ne doit livrer ni la suite de tests, ni les specs
     * OpenAPI (9 Mo à elles deux), ni l'outillage de développement.
     *
     * L'attribut se pose sur le répertoire, pas sur chacun de ses fichiers :
     * c'est ce que `git archive` consulte pour élaguer une branche entière de
     * l'arborescence.
     */
    #[DataProvider('excludedFromArchive')]
    public function testDevelopmentArtefactsAreExcludedFromTheArchive(string $path): void
    {
        self::assertTrue(self::isExportIgnored($path), sprintf('"%s" devrait porter export-ignore.', $path));
    }

    /** @return iterable<string, array{string}> */
    public static function excludedFromArchive(): iterable
    {
        $paths = [
            'tests',
            'docs',
            'bin',
            'examples',
            '.claude',
            'pennylane-api-v2.json',
            'pennylane-api-firm-v1.json',
            '.gitlab-ci.yml',
            'phpunit.xml',
            'phpstan.neon',
            '.php-cs-fixer.php',
            '.gitattributes',
            '.gitignore',
        ];

        foreach ($paths as $path) {
            yield $path => [$path];
        }
    }

    /**
     * Le critère qui compte vraiment, joué sur une archive réelle construite
     * depuis le répertoire de travail — via un index temporaire, pour ne pas
     * toucher à celui du dépôt.
     */
    public function testTheBuiltArchiveShipsTheSourcesWithoutTheDevelopmentPayload(): void
    {
        $entries = self::archiveEntries();

        self::assertContains('src/Pennylane.php', $entries);
        self::assertContains('composer.json', $entries);
        self::assertContains('LICENSE', $entries);
        self::assertContains('CHANGELOG.md', $entries);
        self::assertContains('README.md', $entries);

        foreach ($entries as $entry) {
            self::assertDoesNotMatchRegularExpression(
                '#^(tests|docs|bin|examples|\.claude)/|^pennylane-api-.*\.json$|^\.git(lab-ci\.yml|attributes|ignore)$|^php(unit\.xml|stan\.neon)$|^\.php-cs-fixer\.php$#',
                $entry,
                sprintf('"%s" ne devrait pas figurer dans l\'archive Composer.', $entry),
            );
        }
    }

    #[DataProvider('shippedInArchive')]
    public function testConsumerFacingFilesRemainInTheArchive(string $path): void
    {
        self::assertFalse(self::isExportIgnored($path), sprintf('"%s" ne devrait pas porter export-ignore.', $path));
    }

    /** @return iterable<string, array{string}> */
    public static function shippedInArchive(): iterable
    {
        foreach (['src/Pennylane.php', 'composer.json', 'README.md', 'LICENSE', 'CHANGELOG.md'] as $path) {
            yield $path => [$path];
        }
    }

    public function testNoGitkeepPlaceholderSurvivesInPopulatedDirectories(): void
    {
        foreach (['src/.gitkeep', 'tests/.gitkeep'] as $placeholder) {
            self::assertFileDoesNotExist(self::projectDir().'/'.$placeholder);
        }
    }

    #[DataProvider('examples')]
    public function testEachExampleIsSyntacticallyValid(string $example): void
    {
        $path = self::projectDir().'/examples/'.$example;

        self::assertFileExists($path);

        $output = [];
        $status = 0;
        exec(sprintf('%s -l %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($path)), $output, $status);

        self::assertSame(0, $status, implode("\n", $output));
    }

    #[DataProvider('examples')]
    public function testEachExampleReadsItsCredentialsFromTheEnvironment(string $example): void
    {
        $code = self::read('examples/'.$example);

        self::assertStringContainsString('getenv(', $code, 'Un exemple ne doit jamais porter de jeton en dur.');
        self::assertStringContainsString('declare(strict_types=1);', $code);
    }

    /** @return iterable<string, array{string}> */
    public static function examples(): iterable
    {
        foreach (self::EXAMPLES as $example) {
            yield $example => [$example];
        }
    }

    /**
     * Les exemples sont de la documentation exécutable : ils doivent suivre le
     * style du projet, sous peine de montrer autre chose que ce qu'on écrit.
     */
    public function testExamplesFallWithinTheStyleCheckerScope(): void
    {
        $config = require self::projectDir().'/.php-cs-fixer.php';

        self::assertInstanceOf(ConfigInterface::class, $config);

        $analysed = [];

        foreach ($config->getFinder() as $file) {
            $analysed[] = str_replace(self::projectDir().'/', '', (string) $file);
        }

        self::assertContains('examples/quickstart.php', $analysed);
    }

    /**
     * Trois pans majeurs du SDK — webhooks, upload de fichiers, e-invoicing —
     * étaient absents du README malgré leur présence dans `src/`.
     */
    #[DataProvider('readmeSections')]
    public function testReadmeDocumentsEveryMajorFeature(string $heading): void
    {
        self::assertStringContainsString($heading, self::read('README.md'));
    }

    /** @return iterable<string, array{string}> */
    public static function readmeSections(): iterable
    {
        foreach (['## Webhooks', '## Upload de fichiers', '## E-invoicing', '## Exemples'] as $heading) {
            yield $heading => [$heading];
        }
    }

    private static function projectDir(): string
    {
        return \dirname(__DIR__);
    }

    private static function read(string $relativePath): string
    {
        $path = self::projectDir().'/'.$relativePath;

        self::assertFileExists($path);

        return (string) file_get_contents($path);
    }

    /**
     * `git check-attr` lit le `.gitattributes` du répertoire de travail : la
     * vérification vaut donc avant même le commit, contrairement à un
     * `git archive HEAD`.
     */
    private static function isExportIgnored(string $path): bool
    {
        $output = [];
        $status = 0;

        exec(
            sprintf(
                'git -C %s check-attr export-ignore -- %s 2>&1',
                escapeshellarg(self::projectDir()),
                escapeshellarg($path),
            ),
            $output,
            $status,
        );

        self::assertSame(0, $status, implode("\n", $output));

        return str_ends_with(implode("\n", $output), ': export-ignore: set');
    }

    /**
     * @return list<string> les chemins que `composer require` livrerait,
     *                      répertoires exclus
     */
    private static function archiveEntries(): array
    {
        $index = (string) tempnam(sys_get_temp_dir(), 'pennylane-archive-');
        unlink($index);

        $git = sprintf('GIT_INDEX_FILE=%s git -C %s', escapeshellarg($index), escapeshellarg(self::projectDir()));

        try {
            self::shell($git.' add --all');
            $tree = trim(implode('', self::shell($git.' write-tree')));
            $entries = self::shell(sprintf('%s archive --format=tar %s | tar -t', $git, escapeshellarg($tree)));
        } finally {
            if (file_exists($index)) {
                unlink($index);
            }
        }

        return array_values(array_filter(
            $entries,
            static fn (string $entry): bool => '' !== $entry && !str_ends_with($entry, '/'),
        ));
    }

    /** @return list<string> */
    private static function shell(string $command): array
    {
        $output = [];
        $status = 0;

        exec($command.' 2>&1', $output, $status);

        self::assertSame(0, $status, sprintf("La commande « %s » a échoué :\n%s", $command, implode("\n", $output)));

        return $output;
    }
}
