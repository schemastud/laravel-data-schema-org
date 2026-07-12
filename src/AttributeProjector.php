<?php

namespace Schemastud\DataSchemaOrg;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgId;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgProperty;
use Schemastud\DataSchemaOrg\Attributes\SchemaOrgType;
use Schemastud\DataSchemaOrg\Contracts\SchemaOrgProjector;
use Schemastud\JsonReference\Vocabulary;
use Spatie\LaravelData\Data;
use Spatie\SchemaOrg\Type;

/**
 * The reusable primitive: projects any Data class annotated with {@see SchemaOrgType} /
 * {@see SchemaOrgProperty} into a Schema.org node by reflection — zero bespoke code per DTO. A DTO
 * with irreducibly custom mapping registers its own {@see SchemaOrgProjector} instead; this covers
 * the common case, mirroring how the house JSON Schema generator reads DTO attributes.
 */
class AttributeProjector implements SchemaOrgProjector
{
    /**
     * @param  class-string<Data>  $dataClass
     */
    public function __construct(private string $dataClass) {}

    public function subject(): string
    {
        return $this->dataClass;
    }

    public function project(Data $data, ProjectionContext $context): Type
    {
        $reflection = new ReflectionClass($data);

        $node = $this->makeNode($reflection);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $mapping = $property->getAttributes(SchemaOrgProperty::class);

            if ($mapping === []) {
                continue;
            }

            /** @var SchemaOrgProperty $attribute */
            $attribute = $mapping[0]->newInstance();
            $target = $attribute->name ?? $property->getName();

            $node->setProperty($target, $property->getValue($data));
        }

        if ($id = $this->resolveId($data, $reflection, $context)) {
            $node->setProperty(Vocabulary::Id, $id);
        }

        return $node;
    }

    private function makeNode(ReflectionClass $reflection): Type
    {
        $typeAttribute = $reflection->getAttributes(SchemaOrgType::class);

        if ($typeAttribute === []) {
            throw new InvalidArgumentException(
                "[{$reflection->getName()}] has no #[SchemaOrgType] attribute; register a bespoke projector instead."
            );
        }

        /** @var SchemaOrgType $type */
        $type = $typeAttribute[0]->newInstance();
        $class = 'Spatie\\SchemaOrg\\'.$type->type;

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Unknown Schema.org type [{$type->type}].");
        }

        return new $class;
    }

    /**
     * Identity precedence: an explicit override on the context wins, then a #[SchemaOrgId] property,
     * then a default minted from the DTO's schema name and the configured base URI. The `@id` key
     * itself is the base-owned {@see Vocabulary::Id} so it means the same thing everywhere.
     */
    private function resolveId(Data $data, ReflectionClass $reflection, ProjectionContext $context): ?string
    {
        if ($context->id !== null && $context->id !== '') {
            return $context->id;
        }

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getAttributes(SchemaOrgId::class) === []) {
                continue;
            }

            $value = $property->getValue($data);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $this->mintId($data, $context);
    }

    /**
     * Duck-typed on the house `schemaName()` contract (e.g. laravel-data-schemas' SchemaIdentity)
     * rather than hard-importing it — this leaf stays dependency-light and any DTO exposing a
     * static `schemaName()` mints an `@id`. The IRI shape is `<base>/<schemaName>`.
     */
    private function mintId(Data $data, ProjectionContext $context): ?string
    {
        if ($context->baseUri === null || $context->baseUri === '') {
            return null;
        }

        if (! method_exists($data, 'schemaName')) {
            return null;
        }

        return rtrim($context->baseUri, '/').'/'.ltrim($data::schemaName(), '/');
    }
}
