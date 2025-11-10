<?php

declare(strict_types=1);

namespace FSM\Exceptions;

use Exception;

/**
 * Exception thrown when an invalid state is referenced or used.
 *
 * This exception is thrown in scenarios such as:
 * - Attempting to transition to a non-existent state
 * - Referencing a state that hasn't been defined
 * - Using null or empty state names
 */
class InvalidStateException extends Exception
{
    /**
     * Create an exception for an undefined state.
     */
    public static function undefined(string $stateName): self
    {
        return new self("State '{$stateName}' is not defined in the state machine.");
    }

    /**
     * Create an exception for attempting to add a duplicate state.
     */
    public static function duplicate(string $stateName): self
    {
        return new self("State '{$stateName}' already exists in the state machine.");
    }

    /**
     * Create an exception for an empty state name.
     */
    public static function empty(): self
    {
        return new self("State name cannot be empty.");
    }
}

