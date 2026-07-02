<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg\Attributes;

use Attribute;
use Rushing\JsonReference\NodeId;
use Rushing\SchemaOrg\ProjectionContext;

/**
 * Marks the Data property that sources the node's `@id`. When present and non-null, its value is
 * used as the identity IRI verbatim, taking precedence over the minted default (but still below an
 * explicit override on {@see ProjectionContext}). Identity remains a boundary
 * projection — internally the house keeps opaque {@see NodeId} values.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class SchemaOrgId {}
