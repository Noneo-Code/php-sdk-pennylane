<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use PHPUnit\Framework\TestCase;

/**
 * Le garde-fou anti-float lève à l'envoi ; le typage, lui, doit lever à
 * l'écriture. Un DTO qui annonce accepter un `float` promettrait quelque chose
 * que le SDK refuse ensuite d'envoyer — le pire des deux mondes.
 */
final class NoFloatInInputDtosTest extends TestCase
{
    public function testAFractionalQuantityIsWrittenAsADecimalString(): void
    {
        $line = new NewProductInvoiceLine(productId: 42, quantity: '2.5');

        self::assertSame('2.5', $line->toArray()['quantity']);
    }

    /**
     * Balayage structurel plutôt que douze constructions : il vaut aussi pour
     * les DTO qui n'existent pas encore.
     */
    public function testNoDtoOrRequestExposesAFloat(): void
    {
        $offenders = [];

        foreach (self::sourceFiles() as $file) {
            $source = (string) file_get_contents($file);

            if (1 === preg_match('/^\s*(?:public|private|protected)[^;{]*\bfloat\b/m', $source)) {
                $offenders[] = substr($file, strlen(\dirname(__DIR__, 2)) + 1);
            }
        }

        self::assertSame(
            [],
            $offenders,
            'Les montants et quantités s\'écrivent en chaîne décimale : aucun paramètre de DTO ou de Request ne doit être typé float.',
        );
    }

    /** @return iterable<string> */
    private static function sourceFiles(): iterable
    {
        foreach (['Dto', 'Requests'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(\dirname(__DIR__, 2).'/src/'.$directory),
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && 'php' === $file->getExtension()) {
                    yield $file->getPathname();
                }
            }
        }
    }
}
