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
        /** @var \Illuminate\Config\Repository $config */
        $config = app('config');
        /** @var \Illuminate\Cache\CacheManager $cacheManager */
        $cacheManager = app('cache');
        $cache = new LaravelCache($config, $cacheManager->store());

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
