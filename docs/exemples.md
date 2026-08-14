# Exemples exécutables

Le dossier `examples/` contient des scripts exécutables, autonomes, qui lisent
leur jeton dans l'environnement. Ils ne sont pas livrés dans l'archive Composer.

| Script | Ce qu'il montre |
|---|---|
| `quickstart.php` | Identité du jeton, liste filtrée et triée, pagination complète |
| `invoicing_lifecycle.php` | Client → brouillon → finalisation → envoi par e-mail → marquage payé |
| `firm_portfolio.php` | Portefeuille cabinet, scope entreprise, lecture d'écritures |
| `webhook_receiver.php` | Vérification de signature et parsing d'événement, sans framework |
| `einvoice_import.php` | Import d'une facture électronique et suivi de son statut |

```bash
PENNYLANE_TOKEN=… php examples/quickstart.php
PENNYLANE_FIRM_TOKEN=… php examples/firm_portfolio.php
```

Les scripts d'écriture (`invoicing_lifecycle.php`, `einvoice_import.php`) créent
de vraies données : réservez-les à un environnement de test.
