<?php

declare(strict_types=1);

namespace FSM\Exceptions;

use Exception;

/**
 * Exception thrown when an invalid transition is attempted.
 *
 * This exception is thrown in scenarios such as:
 * - No transition defined for a given input symbol from current state
 * - Attempting to add a transition with invalid state references
 * - Duplicate transitions for the same state-symbol combination (in DFA)
 */
class InvalidTransitionException extends Exception
{
    /**
     * Create an exception for a missing transition.
     */
    public static function notDefined(string $fromState, string $symbol): self
    {
        return new self(
            "No transition defined from state '{$fromState}' with input symbol '{$symbol}'."
        );
    }

    /**
     * Create an exception for a duplicate transition (in DFA context).
     */
    public static function duplicate(string $fromState, string $symbol): self
    {
        return new self(
            "Transition already exists from state '{$fromState}' with input symbol '{$symbol}'. " .
            "In a DFA, each state-symbol pair must have exactly one transition."
        );
    }

    /**
     * Create an exception for an invalid symbol.
     */
    public static function invalidSymbol(string $symbol, array $validAlphabet): self
    {
        $validSymbols = implode(', ', array_map(fn($s) => "'{$s}'", $validAlphabet));
        return new self(
            "Invalid input symbol '{$symbol}'. Valid symbols are: {$validSymbols}."
        );
    }
}

