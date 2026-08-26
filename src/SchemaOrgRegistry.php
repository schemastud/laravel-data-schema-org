<?php

namespace Schemastud\DataSchemaOrg;

use Rushing\Popcorn\Registries\Authorizer;
use Rushing\Popcorn\Registries\BasicRegistry;
use Rushing\Popcorn\Registries\Gated;
use Rushing\Popcorn\Registries\IsRegistry;
use Rushing\Popcorn\Registries\Key;
use Rushing\Popcorn\Registries\OnDuplicate;
use Rushing\Popcorn\Registries\Registry;
use Rushing\Popcorn\Registries\RegistryArity;
use Rushing\Popcorn\Registries\RegistryKey;
use Schemastud\DataSchemaOrg\Contracts\SchemaOrgProjector;
use Schemastud\DataSchemaOrg\Exceptions\UnknownProjector;
use Schemastud\JsonReference\DereferencerRegistry;
use Spatie\LaravelData\Data;
use Spatie\SchemaOrg\Type;

/**
 * Dispatch table for Schema.org projectors, keyed by the Data class each handles — the emit-side
 * mirror of {@see DereferencerRegistry}. Dispatch is by class, never by node
 * shape: a DTO resolves to exactly one projector. An unknown class throws rather than guessing.
 *
 * ## The key is the WHOLE class path, not its basename
 *
 * `Key::fromClass()` is the kernel's sanctioned derivation for class-strings and it takes the
 * basename — right where the class-string is the ENTRY and nothing looks up by key. Here the class
 * IS the lookup identity, and two `OfferData` classes in two namespaces would have collided on
 * `offer-data` under {@see OnDuplicate::Supersede}, silently losing a projector at registration.
 * So the namespace becomes the key's segments and the short name keeps `fromClass()`'s kebab rule:
 * `Splicewire\Beam\Commerce\Data\OfferData` → `schema-org.projectors.splicewire.beam.commerce.data.offer-data`.
 * Every historical caller still spells the FQCN and never sees this.
 *
 * @implements Registry<SchemaOrgProjector>
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
        .'names its own key via subject(), so there is no key to put in config. The key is the subject '
        .'class path segment-mapped (`App\\Data\\OfferData` => `app.data.offer-data`), because a '
        .'basename-only derivation would let two same-named DTOs supersede each other.',
    order: 53,
)]
class SchemaOrgRegistry implements Gated, Registry
{
    /** @var BasicRegistry<SchemaOrgProjector> */
    private BasicRegistry $entries;

    public function __construct()
    {
        $this->entries = BasicRegistry::for($this);
    }

    /**
     * Register a projector under the Data class it names via {@see SchemaOrgProjector::subject()}.
     *
     * WIDENED from the contract rather than shadowing it — contravariance, so the historical
     * one-argument self-keying call `register($projector)` keeps working unchanged.
     */
    public function register(RegistryKey|string|SchemaOrgProjector $key, mixed $entry = null, ?string $by = null, ?string $ability = null): static
    {
        if ($key instanceof SchemaOrgProjector) {
            $entry = $key;
            $key = $key->subject();
        }

        $this->entries->register($this->keyFor($key), $entry, $by, $ability);

        return $this;
    }

    /**
     * The projector for a Data class.
     *
     * Port sugar over {@see tryResolve()}, and it keeps throwing {@see UnknownProjector} rather than
     * the kernel's `RegistryMiss` — tower's `ResolveHandler` and `DereferenceController` catch the
     * sibling exception by name, and this is the vocabulary consumers speak.
     *
     * @param  class-string<Data>  $dataClass
     */
    public function for(string $dataClass): SchemaOrgProjector
    {
        return $this->tryResolve($dataClass) ?? throw new UnknownProjector($dataClass);
    }

    /**
     * The registered subject classes, as keys relative to the declared root — keys go relative in and
     * absolute out (ticket 20 D2).
     *
     * @return string[]
     */
    public function subjects(): array
    {
        return $this->entries->relativeKeys();
    }

    /**
     * Look up the projector for the concrete Data instance and project it.
     */
    public function project(Data $data, ?ProjectionContext $context = null): Type
    {
        return $this->for($data::class)->project($data, $context ?? ProjectionContext::fromConfig());
    }

    /** @param  RegistryKey|class-string<Data>|string  $key */
    public function has(RegistryKey|string $key): bool
    {
        return $this->entries->has($this->keyFor($key));
    }

    public function resolve(RegistryKey|string $key): mixed
    {
        return $this->entries->resolve($this->keyFor($key));
    }

    public function tryResolve(RegistryKey|string $key): mixed
    {
        return $this->entries->tryResolve($this->keyFor($key));
    }

    public function matches(RegistryKey|string $key): array
    {
        return $this->entries->matches($this->keyFor($key));
    }

    public function keys(): array
    {
        return $this->entries->keys();
    }

    public function unfiltered(): Registry
    {
        return $this->entries->unfiltered();
    }

    public function authorizeWith(?Authorizer $authorizer): static
    {
        $this->entries->authorizeWith($authorizer);

        return $this;
    }

    /**
     * Every key argument's one entrance: a class-string becomes its segment-mapped key, anything else
     * is already an address and passes through to the kernel's own door.
     */
    private function keyFor(RegistryKey|string $key): RegistryKey
    {
        if ($key instanceof RegistryKey) {
            return $key;
        }

        if (! str_contains($key, '\\') && ! class_exists($key)) {
            return Key::parse($key);
        }

        return Key::fromSegments(array_map(
            fn (string $part): string => strtolower((string) preg_replace(
                '/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/',
                '-',
                $part,
            )),
            explode('\\', ltrim($key, '\\')),
        ));
    }
}
