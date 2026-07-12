<?php

namespace Schemastud\DataSchemaOrg\Tests\Fixtures;

use Schemastud\DataSchemaOrg\Attributes\SchemaOrgId;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgType;
use Spatie\LaravelData\Data;

/**
 * A fixture with no SchemaIdentity: identity is sourced from an explicit #[SchemaOrgId] property.
 */
#[SchemaOrgType('Person')]
class PersonData extends Data
{
    public function __construct(
        #[SchemaOrgProperty('name')]
        public string $name,
        #[SchemaOrgId]
        public ?string $profileUrl = null,
    ) {}
}
