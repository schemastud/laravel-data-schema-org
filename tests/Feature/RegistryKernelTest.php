<?php

use Rushing\Popcorn\Registries\Registry;
use Rushing\Popcorn\Registries\RegistryIndex;
use Schemastud\DataSchemaOrg\AttributeProjector;
use Schemastud\DataSchemaOrg\SchemaOrgRegistry;
use Schemastud\DataSchemaOrg\Tests\Fixtures\ArticleData;
use Schemastud\DataSchemaOrg\Tests\Fixtures\PersonData;

// The tripwire (registry-kernel ticket 27 D3). An unshared RegistryIndex is auto-resolvable, so the
// describe assertion below would pass over a throwaway index. This is the assertion that cannot.
it('shares one RegistryIndex across the container', function () {
    expect(app(RegistryIndex::class))->toBe(app(RegistryIndex::class));
});

it('describes schema-org.projectors into the index at boot', function () {
    $index = app(RegistryIndex::class);

    expect($index->has('schema-org.projectors'))->toBeTrue()
        ->and($index->resolve('schema-org.projectors'))->toBe(app(SchemaOrgRegistry::class));
});

it('round-trips a projector through the port vocabulary and the contract alike', function () {
    $projector = new AttributeProjector(ArticleData::class);
    $registry = (new SchemaOrgRegistry)->register($projector);

    expect($registry)->toBeInstanceOf(Registry::class)
        ->and($registry->for(ArticleData::class))->toBe($projector)
        ->and($registry->resolve(ArticleData::class))->toBe($projector)
        ->and($registry->tryResolve(PersonData::class))->toBeNull()
        ->and($registry->subjects())->toBe(['schemastud.data-schema-org.tests.fixtures.article-data'])
        ->and((string) $registry->keys()[0])
        ->toBe('schema-org.projectors.schemastud.data-schema-org.tests.fixtures.article-data');
});

// The whole class path is the key, not `Key::fromClass()`'s basename. Two same-named DTOs in two
// namespaces are two entries, where a basename derivation would have superseded one with the other.
it('keeps two same-named subject classes apart', function () {
    $one = new class(ArticleData::class) extends AttributeProjector
    {
        public function subject(): string
        {
            return 'App\\Alpha\\OfferData';
        }
    };

    $two = new class(ArticleData::class) extends AttributeProjector
    {
        public function subject(): string
        {
            return 'App\\Beta\\OfferData';
        }
    };

    $registry = (new SchemaOrgRegistry)->register($one)->register($two);

    expect($registry->has('App\\Alpha\\OfferData'))->toBeTrue()
        ->and($registry->has('App\\Beta\\OfferData'))->toBeTrue()
        ->and($registry->for('App\\Alpha\\OfferData'))->toBe($one)
        ->and($registry->for('App\\Beta\\OfferData'))->toBe($two)
        ->and($registry->keys())->toHaveCount(2);
});
