<?php

namespace Schemastud\DataSchemaOrg\Attributes;

use Attribute;
use Schemastud\DataSchemaOrg\AttributeProjector;
use Spatie\SchemaOrg\Schema;

/**
 * Declares the Schema.org type a Data class projects to (e.g. `Article`, `Product`). The value is
 * the bare type name; {@see AttributeProjector} maps it to the matching
 * {@see Schema} factory. Domain vocabulary lives on the host DTO by design —
 * never in this leaf's src/.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SchemaOrgType
{
    public function __construct(public string $type) {}
}
