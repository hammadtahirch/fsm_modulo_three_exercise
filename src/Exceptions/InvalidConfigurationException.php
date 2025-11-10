<?php

declare(strict_types=1);

namespace FSM\Exceptions;

use Exception;

/**
 * Exception thrown when a state machine is configured incorrectly.
 *
 * This exception is thrown in scenarios such as:
 * - No states defined
 * - No initial state set
 * - No accepting states defined
 * - Empty alphabet
 * - Missing required transitions (incomplete DFA)
 */
class InvalidConfigurationException extends Exception
{
    /**
     * Create an exception for missing initial state.
     */
    public static function noInitialState(): self
    {
        return new self("State machine must have an initial state defined.");
    }

    /**
     * Create an exception for no states defined.
     */
    public static function noStates(): self
    {
        return new self("State machine must have at least one state defined.");
    }

    /**
     * Create an exception for empty alphabet.
     */
    public static function emptyAlphabet(): self
    {
        return new self("State machine must have at least one symbol in its alphabet.");
    }

    /**
     * Create an exception for incomplete DFA (missing transitions).
     */
    public static function incompleteTransitions(string $state, array $missingSymbols): self
    {
        $symbols = implode(', ', array_map(fn($s) => "'{$s}'", $missingSymbols));
        return new self(
            "Incomplete DFA: State '{$state}' is missing transitions for symbols: {$symbols}. " .
            "In a DFA, every state must have a transition for every symbol in the alphabet."
        );
    }

    /**
     * Create an exception for when building is attempted on an already built machine.
     */
    public static function alreadyBuilt(): self
    {
        return new self("State machine has already been built and is immutable.");
    }
}

