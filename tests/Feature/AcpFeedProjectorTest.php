<?php

use Schemastud\DataSchemaOrg\Acp\AcpFeedRegistry;
use Schemastud\DataSchemaOrg\Acp\AttributeAcpFeedProjector;
use Schemastud\DataSchemaOrg\AttributeProjector;
use Schemastud\DataSchemaOrg\SchemaOrgRegistry;
use Schemastud\DataSchemaOrg\Support\LdJson;
use Schemastud\DataSchemaOrg\Tests\Fixtures\OfferFeedData;

function offer(): OfferFeedData
{
    return new OfferFeedData(
        id: 'demo-offer',
        title: 'Demo Offer',
        description: 'A tracer offer.',
        link: 'https://shop.test/offers/demo-offer',
        price: '9.00 USD',
        availability: 'in_stock',
    );
}

it('projects one DTO to the ACP product-feed shape', function () {
    $item = (new AttributeAcpFeedProjector(OfferFeedData::class))->project(offer());

    expect($item)->toBe([
        'id' => 'demo-offer',
        'title' => 'Demo Offer',
        'description' => 'A tracer offer.',
        'link' => 'https://shop.test/offers/demo-offer',
        'price' => '9.00 USD',
        'availability' => 'in_stock',
        'enable_checkout' => true,
    ]);
});

it('omits null-valued feed fields', function () {
    $item = (new AttributeAcpFeedProjector(OfferFeedData::class))
        ->project(new OfferFeedData(id: 'x', title: 'X'));

    expect($item)->toHaveKeys(['id', 'title', 'enable_checkout'])
        ->and($item)->not->toHaveKey('price')
        ->and($item)->not->toHaveKey('availability');
});

it('builds a products feed from a set of DTOs via the registry', function () {
    $registry = app(AcpFeedRegistry::class)
        ->register(new AttributeAcpFeedProjector(OfferFeedData::class));

    $products = $registry->feed([offer(), new OfferFeedData(id: 'b', title: 'B')]);

    expect($products)->toHaveCount(2)
        ->and($products[0]['id'])->toBe('demo-offer')
        ->and($products[1]['id'])->toBe('b');
});

it('projects the SAME DTO to schema.org JSON-LD from one source of truth', function () {
    // The schema.org twin: same OfferFeedData, the JSON-LD boundary projection.
    app(SchemaOrgRegistry::class)->register(new AttributeProjector(OfferFeedData::class));
    $node = app(SchemaOrgRegistry::class)->project(offer());

    $json = json_decode(LdJson::encode($node), true);

    expect($json['@type'])->toBe('Product')
        ->and($json['name'])->toBe('Demo Offer')
        ->and($json['@id'])->toBe('demo-offer');
});
