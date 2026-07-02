<?php

declare(strict_types=1);

use Rushing\DataSchemaOrg\SchemaOrgGraph;
use Rushing\DataSchemaOrg\Support\LdJson;
use Rushing\JsonReference\Vocabulary;
use Spatie\SchemaOrg\Schema;

it('assembles multiple nodes into a single @context + @graph document', function () {
    $graph = (new SchemaOrgGraph)
        ->add(Schema::article()->headline('One'))
        ->add(Schema::person()->name('Ada'));

    $array = $graph->toArray();

    expect($array)->toHaveKey('@context', 'https://schema.org')
        ->and($array['@graph'])->toHaveCount(2);
});

it('emits @context once at the root, never per node', function () {
    $array = (new SchemaOrgGraph)
        ->add(Schema::article()->headline('One'))
        ->toArray();

    expect($array['@graph'][0])->not->toHaveKey('@context')
        ->and($array['@graph'][0])->toHaveKey('@type', 'Article');
});

it('de-duplicates nodes that share an @id', function () {
    $a = Schema::article()->headline('First telling')->setProperty(Vocabulary::Id, 'https://example.test/x');
    $b = Schema::article()->headline('Second telling')->setProperty(Vocabulary::Id, 'https://example.test/x');

    $array = (new SchemaOrgGraph)->add($a)->add($b)->toArray();

    expect($array['@graph'])->toHaveCount(1);
});

it('keeps distinct nodes without an @id', function () {
    $array = (new SchemaOrgGraph)
        ->add(Schema::thing()->name('anon one'))
        ->add(Schema::thing()->name('anon two'))
        ->toArray();

    expect($array['@graph'])->toHaveCount(2);
});

it('renders a graph array through LdJson', function () {
    $graph = (new SchemaOrgGraph)->add(Schema::article()->headline('One'));

    $script = LdJson::script($graph);

    expect($script)->toStartWith('<script type="application/ld+json">')
        ->and($script)->toContain('"@graph"')
        ->and($script)->toEndWith('</script>');
});
