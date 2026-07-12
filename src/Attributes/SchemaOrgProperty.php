<?php

namespace Schemastud\DataSchemaOrg\Attributes;

use Attribute;

/**
 * Maps a Data property to a Schema.org property on the projected node. Without a name the DTO
 * property name is used verbatim; pass a name to translate (e.g. `title` → `headline`). A null
 * value on the DTO is omitted from the node.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SchemaOrgProperty
{
    public function __construct(public ?string $name = null) {}
}
