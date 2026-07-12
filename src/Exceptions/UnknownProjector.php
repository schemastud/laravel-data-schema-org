<?php

namespace Schemastud\DataSchemaOrg\Exceptions;

use RuntimeException;

/**
 * Thrown when the registry is asked to project a Data class no projector was registered for.
 * Loud by design — a missing projection is a wiring gap, not a silent no-op.
 */
class UnknownProjector extends RuntimeException
{
    public function __construct(public string $dataClass)
    {
        parent::__construct("No Schema.org projector registered for [{$dataClass}].");
    }
}
