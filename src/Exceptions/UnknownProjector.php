<?php

declare(strict_types=1);

namespace Rushing\SchemaOrg\Exceptions;

use RuntimeException;

/**
 * Thrown when the registry is asked to project a Data class no projector was registered for.
 * Loud by design — a missing projection is a wiring gap, not a silent no-op.
 */
final class UnknownProjector extends RuntimeException
{
    public function __construct(public readonly string $dataClass)
    {
        parent::__construct("No Schema.org projector registered for [{$dataClass}].");
    }
}
