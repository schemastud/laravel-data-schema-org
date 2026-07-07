<?php

namespace Rushing\DataSchemaOrg\Support;

use Rushing\DataSchemaOrg\SchemaOrgGraph;
use Spatie\SchemaOrg\Type;

/**
 * Renders a projected node / graph / array into a `<script type="application/ld+json">` block.
 * Escapes `</script>` (via `JSON_HEX_TAG`) so an embedded string can't break out of the tag, and
 * keeps slashes/unicode unescaped for clean, crawler-friendly output. Render-agnostic: the package
 * core returns data; this is one optional way to put it on a page.
 */
class LdJson
{
    public const Flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;

    /**
     * @param  Type|SchemaOrgGraph|array<mixed>  $node
     */
    public static function encode(Type|SchemaOrgGraph|array $node): string
    {
        return (string) json_encode(self::toArray($node), self::Flags);
    }

    /**
     * @param  Type|SchemaOrgGraph|array<mixed>  $node
     */
    public static function script(Type|SchemaOrgGraph|array $node, ?string $nonce = null): string
    {
        $nonceAttr = $nonce !== null ? ' nonce="'.e($nonce).'"' : '';

        return '<script type="application/ld+json"'.$nonceAttr.'>'.self::encode($node).'</script>';
    }

    /**
     * @param  Type|SchemaOrgGraph|array<mixed>  $node
     * @return array<mixed>
     */
    public static function toArray(Type|SchemaOrgGraph|array $node): array
    {
        return match (true) {
            $node instanceof Type => $node->toArray(),
            $node instanceof SchemaOrgGraph => $node->toArray(),
            default => $node,
        };
    }
}
