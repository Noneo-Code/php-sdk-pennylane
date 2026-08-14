<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tooling\Coverage;

/**
 * Depuis quel connecteur une opération de la spec se déclenche. Une même classe
 * `Request` sert plusieurs portées — le préfixe `/companies/{id}` est porté par
 * `CompanyScope`, pas par la requête — et n'est donc joignable que là où une
 * ressource l'expose.
 */
enum RequestScope: string
{
    /** Company API v2, depuis `Pennylane`. */
    case Company = 'company';

    /** Firm API v1 au niveau du cabinet, depuis `PennylaneFirm`. */
    case Firm = 'firm';

    /** Firm API v1 scopée à une entreprise, depuis `CompanyScope`. */
    case FirmCompany = 'firm_company';

    public static function ofPath(string $path): self
    {
        if (1 === preg_match('#^/api/external/firm/v1/companies/\{[^}]+\}/#', $path)) {
            return self::FirmCompany;
        }

        if (str_starts_with($path, '/api/external/firm/v1')) {
            return self::Firm;
        }

        return self::Company;
    }
}
