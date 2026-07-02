<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg\Tests\Fixtures;

use Rushing\SchemaOrg\Attributes\SchemaOrgId;
use Rushing\SchemaOrg\Attributes\SchemaOrgProperty;
use Rushing\SchemaOrg\Attributes\SchemaOrgType;
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
