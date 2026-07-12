<?php

use Schemastud\DataSchemaOrg\AttributeProjector;
use Schemastud\DataSchemaOrg\Exceptions\UnknownProjector;
use Schemastud\DataSchemaOrg\SchemaOrgRegistry;
use Schemastud\DataSchemaOrg\Tests\Fixtures\ArticleData;
use Schemastud\DataSchemaOrg\Tests\Fixtures\PersonData;
use Spatie\SchemaOrg\Article;

it('dispatches a projector by Data class, never by node shape', function () {
    $projector = new AttributeProjector(ArticleData::class);
    $registry = (new SchemaOrgRegistry)->register($projector);

    expect($registry->has(ArticleData::class))->toBeTrue()
        ->and($registry->for(ArticleData::class))->toBe($projector)
        ->and($registry->has(PersonData::class))->toBeFalse();
});

it('throws UnknownProjector for an unregistered Data class', function () {
    (new SchemaOrgRegistry)->for(PersonData::class);
})->throws(UnknownProjector::class);

it('projects a registered Data instance end-to-end', function () {
    $registry = (new SchemaOrgRegistry)->register(new AttributeProjector(ArticleData::class));

    $node = $registry->project(new ArticleData(title: 'Hello World'));

    expect($node)->toBeInstanceOf(Article::class)
        ->and($node->toArray())
        ->toMatchArray([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Hello World',
        ]);
});
