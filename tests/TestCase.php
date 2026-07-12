<?php

namespace Schemastud\DataSchemaOrg\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Schemastud\DataSchemaOrg\ServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            ServiceProvider::class,
        ];
    }
}
