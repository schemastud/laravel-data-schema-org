<?php

namespace Rushing\DataSchemaOrg\Tests\Fixtures;

use Rushing\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Rushing\DataSchemaOrg\Attributes\SchemaOrgType;
use Spatie\LaravelData\Data;

/**
 * Mirrors a house DTO: exposes a static schemaName() (the laravel-data-schemas SchemaIdentity
 * shape) which the projector duck-types on to mint an @id.
 */
#[SchemaOrgType('Article')]
class ArticleData extends Data
{
    /**
     * @param  array<int, string>  $tags
     */
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

    public static function schemaName(): string
    {
        return 'test/article';
    }

    public static function schemaVersion(): int
    {
        return 1;
    }
}
