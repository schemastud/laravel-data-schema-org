<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-schema-org')
            ->hasConfigFile('schema-org');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SchemaOrgRegistry::class);
    }

    public function packageBooted(): void
    {
        Blade::directive('ldjson', fn (string $expression): string => "<?php echo \\Rushing\\SchemaOrg\\Support\\LdJson::script({$expression}); ?>");
    }
}
