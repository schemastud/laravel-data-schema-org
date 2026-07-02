<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rushing\SchemaOrg\ServiceProvider;
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
