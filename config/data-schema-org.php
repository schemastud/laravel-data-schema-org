<?php

return [
    /*
     * Base URI used to mint default `@id` IRIs for projected nodes (combined with a DTO's
     * schema name). Leave empty to omit minted identity; hosts that already carry a NodeId
     * pass it explicitly on the ProjectionContext instead.
     */
    'base_uri' => env('DATA_SCHEMA_ORG_BASE_URI', env('APP_URL')),

    /*
     * The JSON-LD @context emitted at the root of an assembled graph.
     */
    'context' => 'https://schema.org',

    /*
     * Optional default locale carried on the ProjectionContext.
     */
    'locale' => null,
];
