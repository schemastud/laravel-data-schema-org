<?php

declare(strict_types=1);

use Rushing\JsonReference\Vocabulary;
use Rushing\SchemaOrg\AttributeProjector;
use Rushing\SchemaOrg\ProjectionContext;
use Rushing\SchemaOrg\Tests\Fixtures\ArticleData;
use Rushing\SchemaOrg\Tests\Fixtures\PersonData;

it('reflects the type and maps annotated properties', function () {
    $node = (new AttributeProjector(ArticleData::class))->project(
        new ArticleData(title: 'A Post', body: 'Body copy', tags: ['a', 'b'], publishedAt: '2026-07-01'),
        new ProjectionContext,
    );

    expect($node->toArray())->toMatchArray([
        '@type' => 'Article',
        'headline' => 'A Post',
        'articleBody' => 'Body copy',
        'keywords' => ['a', 'b'],
        'datePublished' => '2026-07-01',
    ]);
});

it('omits null-valued properties from the node', function () {
    $node = (new AttributeProjector(ArticleData::class))->project(
        new ArticleData(title: 'Only a title'),
        new ProjectionContext,
    );

    expect($node->toArray())->not->toHaveKey('articleBody')
        ->and($node->toArray())->not->toHaveKey('datePublished');
});

it('mints @id from schemaName + configured base URI', function () {
    $node = (new AttributeProjector(ArticleData::class))->project(
        new ArticleData(title: 'Identified'),
        new ProjectionContext(baseUri: 'https://example.test'),
    );

    expect($node->toArray()[Vocabulary::Id])->toBe('https://example.test/test/article');
});

it('omits @id when no base URI is configured', function () {
    $node = (new AttributeProjector(ArticleData::class))->project(
        new ArticleData(title: 'Anonymous'),
        new ProjectionContext,
    );

    expect($node->toArray())->not->toHaveKey(Vocabulary::Id);
});

it('lets an explicit context @id override the minted default', function () {
    $node = (new AttributeProjector(ArticleData::class))->project(
        new ArticleData(title: 'Overridden'),
        new ProjectionContext(baseUri: 'https://example.test', id: 'nod_01ABC'),
    );

    expect($node->toArray()[Vocabulary::Id])->toBe('nod_01ABC');
});

it('sources @id from a #[SchemaOrgId] property when present', function () {
    $node = (new AttributeProjector(PersonData::class))->project(
        new PersonData(name: 'Ada', profileUrl: 'https://example.test/people/ada'),
        new ProjectionContext(baseUri: 'https://example.test'),
    );

    expect($node->toArray()[Vocabulary::Id])->toBe('https://example.test/people/ada')
        ->and($node->toArray()['name'])->toBe('Ada');
});

it('uses the base-owned Vocabulary::Id key, not a local literal', function () {
    expect(Vocabulary::Id)->toBe('@id');
});
