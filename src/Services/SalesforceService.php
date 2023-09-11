<?php

namespace SynergiTech\Salesforce\Services;

use Exception;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;
use Omniphx\Forrest\Providers\Laravel\LaravelCache;
use SynergiTech\Salesforce\Exceptions\AuthenticationFailedException;

class SalesforceService
{
    public static function table(string $tableName): TableService
    {
        $cache = new LaravelCache(app('config'), app('cache')->store());

        if (!$cache->has('token')) {
            self::authenticate();
        }
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
