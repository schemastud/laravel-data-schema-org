<?php

namespace Schemastud\DataSchemaOrg;

use Illuminate\Support\Facades\Blade;
use Rushing\Popcorn\Registries\RegistryIndex;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data-schema-org')
            ->hasConfigFile('data-schema-org');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SchemaOrgRegistry::class);
    }

    public function packageBooted(): void
    {
        // Declaring and indexing are two acts (registry-kernel ticket 21 D1): the #[IsRegistry] says
        // what root the class owns, and this is what puts it IN the index. An owner describes DOWN
        // from its own boot; the index never reaches up.
        $this->app->make(RegistryIndex::class)->describe(
            $this->app->make(SchemaOrgRegistry::class),
            by: self::class,
        );

        Blade::directive('ldjson', fn (string $expression): string => "<?php echo \\Schemastud\\DataSchemaOrg\\Support\\LdJson::script({$expression}); ?>");
    }
}
