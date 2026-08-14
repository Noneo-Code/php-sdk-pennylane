# Tester votre intégration

`Testing\MockPennylane` simule l'API dans vos tests, sans réseau et sans
qu'aucun symbole Saloon n'entre dans votre code. Les réponses s'associent par
méthode HTTP et chemin — celui de la documentation Pennylane, wildcards `*`
admis — ou se servent en file séquentielle (liste sans clés) :

```php
use Noneo\Sdk\Pennylane\Testing\MockPennylane;

$mock = MockPennylane::create([
    'GET /customer_invoices/42' => MockPennylane::json(['id' => 42, /* … */]),
    'POST /customers'           => MockPennylane::json(['id' => 7, /* … */], 201),
    'GET /suppliers/*'          => MockPennylane::error(404),
]);

$pennylane = $mock->pennylane();   // ou $mock->firm() côté cabinet
$monService->facturer($pennylane); // le code sous test, inchangé

$mock->assertSent('POST', '/customers');
$mock->assertNothingElseSent();
```

Côté Firm, les chemins gardent le scope d'entreprise :
`'GET /companies/12/ledger_entries'`. Une requête qu'aucune entrée ne couvre
lève `UnexpectedRequestException` — le test décrit tout ce qui part, ou il
échoue. Les assertions (`assertSent`, `assertNotSent`, `assertNothingSent`,
`assertNothingElseSent`) s'appuient sur PHPUnit, présent dans les
`require-dev` de tout projet qui teste.

Les réponses d'erreur (`MockPennylane::error(404)`) lèvent les mêmes
exceptions que l'API réelle (`NotFoundException`, etc.) : le chemin d'échec se
teste comme le chemin nominal.
