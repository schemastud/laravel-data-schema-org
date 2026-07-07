<?php

namespace Rushing\DataSchemaOrg\Attributes;

use Attribute;
use Rushing\DataSchemaOrg\ProjectionContext;
use Rushing\JsonReference\NodeId;

/**
 * Marks the Data property that sources the node's `@id`. When present and non-null, its value is
 * used as the identity IRI verbatim, taking precedence over the minted default (but still below an
 * explicit override on {@see ProjectionContext}). Identity remains a boundary
 * projection — internally the house keeps opaque {@see NodeId} values.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SchemaOrgId {}
