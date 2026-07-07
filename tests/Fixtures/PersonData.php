<?php

namespace Rushing\DataSchemaOrg\Tests\Fixtures;

use Rushing\DataSchemaOrg\Attributes\SchemaOrgId;
use Rushing\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Rushing\DataSchemaOrg\Attributes\SchemaOrgType;
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
