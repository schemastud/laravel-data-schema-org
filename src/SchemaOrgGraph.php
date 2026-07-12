<?php

namespace Schemastud\DataSchemaOrg;

use Schemastud\JsonReference\Vocabulary;
use Spatie\SchemaOrg\Type;

/**
 * Assembles many projected nodes into one JSON-LD document — a single shared `@context` at the
 * root and an `@graph` array of nodes. Nodes are de-duplicated by `@id`: because {@see Vocabulary::Id}
 * is stable instance identity, two projections of the same entity collapse to one graph entry.
 * Nodes without an `@id` are always kept (nothing to merge them on).
 */
class SchemaOrgGraph
{
    /** @var array<int, Type> */
    private array $nodes = [];

    public function add(Type $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    /**
     * @return array{"@context": string, "@graph": array<int, array<mixed>>}
     */
    public function toArray(): array
    {
        $seen = [];
        $graph = [];

        foreach ($this->nodes as $node) {
            $array = $node->toArray();
            unset($array['@context']);

            $id = $array[Vocabulary::Id] ?? null;

            if (is_string($id)) {
                if (isset($seen[$id])) {
                    continue;
                }

                $seen[$id] = true;
            }

            $graph[] = $array;
        }

        return [
            '@context' => $this->context(),
            '@graph' => $graph,
        ];
    }

    private function context(): string
    {
        return (string) config('data-schema-org.context', 'https://schema.org');
    }
}
