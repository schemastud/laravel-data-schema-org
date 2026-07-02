<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg\Contracts;

use Rushing\JsonReference\DereferencerRegistry;
use Rushing\SchemaOrg\ProjectionContext;
use Spatie\LaravelData\Data;
use Spatie\SchemaOrg\Type;

/**
 * Projects one Data class into a Schema.org node (JSON-LD) at the boundary. The dual of the
 * house Data-as-source-of-truth standard: the same DTO that generates the TS type and JSON
 * Schema (structure) also projects to the public Schema.org vocabulary (semantics) — orthogonal
 * axes over one artifact.
 *
 * A projector is registered by the Data class it handles ({@see subject()}) and dispatched by
 * class, never by node shape — mirroring how {@see DereferencerRegistry}
 * dispatches by handle.
 */
interface SchemaOrgProjector
{
    /**
     * The fully-qualified Data class this projector handles.
     *
     * @return class-string<Data>
     */
    public function subject(): string;

    /**
     * Project the given Data instance into a Schema.org typed node. The context carries the
     * boundary concerns (base URI/host for minting `@id`, an override identity, locale).
     */
    public function project(Data $data, ProjectionContext $context): Type;
}
