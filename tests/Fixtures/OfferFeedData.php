<?php

namespace Schemastud\DataSchemaOrg\Tests\Fixtures;

use Schemastud\DataSchemaOrg\Acp\Attributes\AcpFeedProperty;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgId;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgType;
use Spatie\LaravelData\Data;

/**
 * One catalog offer annotated for BOTH boundary projections — schema.org JSON-LD (search/agent
 * discoverability) and the ACP product feed (agent checkout) — from a single source of truth.
 */
#[SchemaOrgType('Product')]
class OfferFeedData extends Data
{
    public function __construct(
        #[SchemaOrgId]
        #[AcpFeedProperty('id')]
        public string $id,
        #[SchemaOrgProperty('name')]
        #[AcpFeedProperty('title')]
        public string $title,
        #[SchemaOrgProperty('description')]
        #[AcpFeedProperty('description')]
        public ?string $description = null,
        #[SchemaOrgProperty('url')]
        #[AcpFeedProperty('link')]
        public ?string $link = null,
        #[AcpFeedProperty('price')]
        public ?string $price = null,
        #[AcpFeedProperty('availability')]
        public ?string $availability = null,
        #[AcpFeedProperty('enable_checkout')]
        public bool $enableCheckout = true,
    ) {}

    public static function schemaName(): string
    {
        return 'test/offer';
    }
}
