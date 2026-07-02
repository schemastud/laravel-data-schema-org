<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Rushing\SchemaOrg\SchemaOrgGraph;
use Rushing\SchemaOrg\Support\LdJson;
use Spatie\SchemaOrg\Schema;

it('escapes a </script> sequence so a string cannot break out of the tag', function () {
    $node = Schema::article()->headline('pwn</script><script>alert(1)</script>');

    $script = LdJson::script($node);

    expect($script)->not->toContain('</script><script>')
        ->and($script)->toContain('<');
});

it('keeps slashes and unicode unescaped', function () {
    $node = Schema::article()->headline('café')->url('https://example.test/a/b');

    $encoded = LdJson::encode($node);

    expect($encoded)->toContain('https://example.test/a/b')
        ->and($encoded)->toContain('café');
});

it('accepts a Type, a graph, and a plain array', function () {
    $fromType = LdJson::script(Schema::article()->headline('T'));
    $fromGraph = LdJson::script((new SchemaOrgGraph)->add(Schema::article()->headline('G')));
    $fromArray = LdJson::script(['@type' => 'Article', 'headline' => 'A']);

    expect($fromType)->toContain('"@type":"Article"')
        ->and($fromGraph)->toContain('"@graph"')
        ->and($fromArray)->toContain('"headline":"A"');
});

it('renders the @ldjson blade directive to a valid script block', function () {
    $rendered = Blade::render('@ldjson($node)', ['node' => Schema::article()->headline('Directive')]);

    expect($rendered)->toStartWith('<script type="application/ld+json">')
        ->and($rendered)->toContain('"headline":"Directive"')
        ->and($rendered)->toEndWith('</script>');
});
