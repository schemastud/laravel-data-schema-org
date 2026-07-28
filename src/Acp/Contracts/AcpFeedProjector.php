<?php

namespace Schemastud\DataSchemaOrg\Acp\Contracts;

use Schemastud\DataSchemaOrg\Contracts\SchemaOrgProjector;
use Spatie\LaravelData\Data;

/**
 * Projects one Data class into an Agentic Commerce Protocol product-feed item (a plain associative
 * array, ready to serialize into the feed's `products` list). The ACP-feed twin of
 * {@see SchemaOrgProjector}: same registry-by-class dispatch,
 * different boundary artifact — JSON-LD semantics there, agent-checkout feed shape here.
 */
interface AcpFeedProjector
{
    /**
     * The fully-qualified Data class this projector handles.
     *
     * @return class-string<Data>
     */
    public function subject(): string;

    /**
     * @return array<string, mixed>
     */
    public function project(Data $data): array;
}
