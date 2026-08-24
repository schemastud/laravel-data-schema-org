<?php

namespace Schemastud\DataSchemaOrg;

use Rushing\Popcorn\Registries\IsRegistry;
use Rushing\Popcorn\Registries\OnDuplicate;
use Rushing\Popcorn\Registries\RegistryArity;
use Schemastud\DataSchemaOrg\Contracts\SchemaOrgProjector;
use Schemastud\DataSchemaOrg\Exceptions\UnknownProjector;
use Schemastud\JsonReference\DereferencerRegistry;
use Spatie\LaravelData\Data;
use Spatie\SchemaOrg\Type;

/**
 * Dispatch table for Schema.org projectors, keyed by the Data class each handles — the emit-side
 * mirror of {@see DereferencerRegistry}. Dispatch is by class, never by node
 * shape: a DTO resolves to exactly one projector. An unknown class throws rather than guessing.
 */
#[IsRegistry(
    root: 'schema-org.projectors',
    of: 'schema.org projectors keyed by the Data class each handles — the emit-side pair of the '
        .'dereferencer registry',
    arity: RegistryArity::PickOne,
    entryType: SchemaOrgProjector::class,
    onDuplicate: OnDuplicate::Supersede,
    note: 'PickOne for the reason `AcpFeedRegistry` spells out: `project()` LOOKS like a fan-out and is '
        .'not one — it dispatches ONE instance by $data::class to exactly one projector. The projector '
        .'names its own key via subject(), so there is no key to put in config.',
    order: 53,
)]
class SchemaOrgRegistry
{
    /** @var array<class-string<Data>, SchemaOrgProjector> */
    private array $byClass = [];

    public function register(SchemaOrgProjector $projector): self
    {
        $this->byClass[$projector->subject()] = $projector;

        return $this;
    }

    /**
     * @param  class-string<Data>  $dataClass
     */
    public function for(string $dataClass): SchemaOrgProjector
    {
        return $this->byClass[$dataClass] ?? throw new UnknownProjector($dataClass);
    }

    /**
     * @param  class-string<Data>  $dataClass
     */
    public function has(string $dataClass): bool
    {
        return isset($this->byClass[$dataClass]);
    }

    /**
     * Look up the projector for the concrete Data instance and project it.
     */
    public function project(Data $data, ?ProjectionContext $context = null): Type
    {
        return $this->for($data::class)->project($data, $context ?? ProjectionContext::fromConfig());
    }
}
