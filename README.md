# Laravel Data Schema.org

Project [Spatie Laravel Data](https://github.com/spatie/laravel-data) objects into
[Schema.org](https://schema.org) nodes (JSON-LD), using
[spatie/schema-org](https://github.com/spatie/schema-org) as the typed builder.

Your Data object is already the source of truth for structure (TypeScript types, JSON Schema). This
package adds one more projection off that same object: the public **semantic** vocabulary browsers
and crawlers read. Structure and semantics are separate axes over one DTO — this package owns the
semantic one, at the boundary, without changing your internal model.

## How it works

A `Data` class declares its Schema.org mapping with attributes. A registry dispatches a DTO to the
projector registered for its class and returns a `Spatie\SchemaOrg\Type`, which you render as a
JSON-LD `<script>` block. Identity (`@id`) is reused from
[schemastud/laravel-json-reference](https://github.com/schemastud/laravel-json-reference), so a node
means the same thing across the system.

The package projects only. It never becomes your house model and never assumes a render surface.

## Installation

```bash
composer require schemastud/laravel-data-schema-org
```

The config file is optional:

```bash
php artisan vendor:publish --tag=data-schema-org-config
```

```php
// config/data-schema-org.php
return [
    // Base URI for minted @id IRIs. Empty = omit minted identity.
    'base_uri' => env('DATA_SCHEMA_ORG_BASE_URI', env('APP_URL')),

    // The @context emitted at the root of an assembled graph.
    'context' => 'https://schema.org',

    'locale' => null,
];
```

## Usage

### 1. Annotate a Data object

```php
use Rushing\DataSchemaOrg\Attributes\SchemaOrgType;
use Rushing\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Spatie\LaravelData\Data;

#[SchemaOrgType('Article')]
class ArticleData extends Data
{
    public function __construct(
        #[SchemaOrgProperty('headline')]
        public string $title,
        #[SchemaOrgProperty('articleBody')]
        public ?string $body = null,
        #[SchemaOrgProperty('keywords')]
        public array $tags = [],
        #[SchemaOrgProperty('datePublished')]
        public ?string $publishedAt = null,
    ) {}
}
```

`#[SchemaOrgType]` names the Schema.org type. `#[SchemaOrgProperty]` maps a DTO property to a
Schema.org property — omit the name to use the property name verbatim. Null values are dropped.

### 2. Register the projector

The attribute-driven `AttributeProjector` handles any annotated DTO with no bespoke code. Register
it once, typically in a service provider:

```php
use Rushing\DataSchemaOrg\AttributeProjector;
use Rushing\DataSchemaOrg\SchemaOrgRegistry;

public function boot(): void
{
    $this->app->make(SchemaOrgRegistry::class)
        ->register(new AttributeProjector(ArticleData::class));
}
```

### 3. Project

```php
use Rushing\DataSchemaOrg\ProjectionContext;
use Rushing\DataSchemaOrg\SchemaOrgRegistry;

$node = app(SchemaOrgRegistry::class)->project(
    $article,
    new ProjectionContext(baseUri: 'https://example.com'),
);

$node->toArray();
// [
//   '@context' => 'https://schema.org',
//   '@type'    => 'Article',
//   'headline' => '...',
//   '@id'      => 'https://example.com/...',
// ]
```

### 4. Render

Blade:

```blade
@ldjson($node)
```

React / Inertia — attach the array to a prop and render it in `<Head>`:

```php
return inertia('Post/Show', ['jsonLd' => $node->toArray()]);
```

```tsx
<Head>
  <script type="application/ld+json"
    dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
</Head>
```

Or render the string directly with the helper, which escapes any `</script>` in the content:

```php
use Rushing\DataSchemaOrg\Support\LdJson;

LdJson::script($node);          // full <script> block
LdJson::encode($node);          // just the JSON
LdJson::script($node, nonce: $cspNonce);
```

## Identity (`@id`)

`@id` is resolved in this order:

1. An `id` set on the `ProjectionContext` (e.g. a serialized `NodeId` your host already holds).
2. A DTO property marked `#[SchemaOrgId]`.
3. A default minted as `{base_uri}/{schemaName}` when the DTO exposes a static `schemaName()`
   (the [laravel-data-schemas](https://github.com/schemastud/laravel-data-schemas) `SchemaIdentity`
   contract) and a base URI is configured.

The key itself is `Rushing\JsonReference\Vocabulary::Id`, not a local literal — the IRI is a
boundary projection; your internal identity stays opaque.

## Graphs

Assemble several nodes into one JSON-LD document. Nodes sharing an `@id` collapse to one entry;
`@context` is emitted once at the root.

```php
use Rushing\DataSchemaOrg\SchemaOrgGraph;
use Spatie\SchemaOrg\Schema;

$graph = (new SchemaOrgGraph)
    ->add(Schema::article()->headline('...'))
    ->add(Schema::person()->name('...'));

LdJson::script($graph);
// { "@context": "https://schema.org", "@graph": [ ... ] }
```

## Custom projectors

When attribute mapping isn't enough, implement the contract and register it the same way:

```php
use Rushing\DataSchemaOrg\Contracts\SchemaOrgProjector;
use Rushing\DataSchemaOrg\ProjectionContext;
use Spatie\LaravelData\Data;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

final class ProductProjector implements SchemaOrgProjector
{
    public function subject(): string
    {
        return ProductData::class;
    }

    public function project(Data $data, ProjectionContext $context): Type
    {
        return Schema::product()
            ->name($data->name)
            ->offers(Schema::offer()->price($data->price)->priceCurrency($data->currency));
    }
}
```

Dispatch is by Data class, never by node shape. An unregistered class throws `UnknownProjector`
rather than guessing.

## Requirements

- PHP 8.3+
- Laravel 11, 12, or 13

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
