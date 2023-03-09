<?php

namespace SynergiTech\Salesforce\Facades;

use Illuminate\Support\Facades\Facade;
use SynergiTech\Salesforce\Services\SalesforceService;

class Salesforce extends Facade
{
    /**
     * Get the registered class for the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return SalesforceService::class;
    }
}
