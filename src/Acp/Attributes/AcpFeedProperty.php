<?php

namespace Schemastud\DataSchemaOrg\Acp\Attributes;

use Attribute;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgProperty;

/**
 * Maps a Data property onto a field of an Agentic Commerce Protocol product-feed item. Without a
 * name the DTO property name is used verbatim; pass a name to translate to the ACP field (e.g.
 * `imageLink` → `image_link`). A null value is omitted from the projected item. The dual of
 * {@see SchemaOrgProperty} — the same DTO projects to both the
 * schema.org JSON-LD (search/agent discoverability) and the ACP feed (agent checkout) from one
 * source of truth. Domain vocabulary lives on the host DTO; this leaf stays domain-agnostic.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AcpFeedProperty
{
    public function __construct(public ?string $name = null) {}
}
