<?php

namespace Schemastud\DataSchemaOrg;

use Illuminate\Support\Facades\Blade;
use Schemastud\DataSchemaOrg\Acp\AcpFeedRegistry;
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

        // The ACP product-feed twin of the JSON-LD registry: a host registers a
        // projector per catalog DTO to emit the agent-checkout feed shape.
        $this->app->singleton(AcpFeedRegistry::class);
    }

    public function packageBooted(): void
    {
        Blade::directive('ldjson', fn (string $expression): string => "<?php echo \\Schemastud\\DataSchemaOrg\\Support\\LdJson::script({$expression}); ?>");
    }
}
