<?php

namespace SynergiTech\Salesforce\Services;

use Exception;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;
use SynergiTech\Salesforce\Exceptions\AuthenticationFailedException;

class SalesforceService
{
    public static function table(string $tableName): TableService
    {
        self::authenticate();
        return new TableService($tableName);
    }

    protected static function authenticate(): void
    {
        try {
            Forrest::authenticate();
        } catch (Exception $ex) {
            throw new AuthenticationFailedException('Unable to authenticate with Salesforce: ' . $ex->getMessage());
        }
    }
}
