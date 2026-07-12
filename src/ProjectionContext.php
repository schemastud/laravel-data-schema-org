<?php

namespace Schemastud\DataSchemaOrg;

use Schemastud\JsonReference\NodeId;

/**
 * The boundary concerns a projection needs but a DTO shouldn't carry: the base URI used to mint
 * `@id` IRIs, an explicit identity override (e.g. a serialized {@see NodeId}
 * when the host already has one), and the locale. Immutable; `withId()` / `withBaseUri()` return copies.
 */
class ProjectionContext
{
    public function __construct(
        public ?string $baseUri = null,
        public ?string $id = null,
        public ?string $locale = null,
    ) {}

    /**
     * Build a context from published config defaults, letting callers override any field.
     */
    public static function fromConfig(?string $id = null, ?string $locale = null): self
    {
        return new self(
            baseUri: (string) config('data-schema-org.base_uri', '') ?: null,
            id: $id,
            locale: $locale ?? (config('data-schema-org.locale') ? (string) config('data-schema-org.locale') : null),
        );
    }

    public function withId(string $id): self
    {
        return new self($this->baseUri, $id, $this->locale);
    }

    public function withBaseUri(string $baseUri): self
    {
        return new self($baseUri, $this->id, $this->locale);
    }
}
