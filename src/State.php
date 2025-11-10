<?php

declare(strict_types=1);

namespace FSM;

use FSM\Exceptions\InvalidStateException;

/**
 * Represents a single state in a finite state machine.
 *
 * A state is characterized by:
 * - A unique name/identifier
 * - Whether it is an accepting/final state
 *
 * This class is immutable once created to ensure state machine consistency.
 *
 * @immutable
 */
class State
{
    /**
     * @param string $name The unique identifier for this state
     * @param bool $isAccepting Whether this is an accepting/final state
     * @throws InvalidStateException If the state name is empty
     */
    public function __construct(
        private readonly string $name,
        private readonly bool $isAccepting = false
    ) {
        // Can't have a state with no name - that would be confusing
        if (empty(trim($name))) {
            throw InvalidStateException::empty();
        }
    }

    /**
     * Get the state's name/identifier.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if this is an accepting/final state.
     */
    public function isAccepting(): bool
    {
        return $this->isAccepting;
    }

    /**
     * String representation of the state.
     * 
     * Useful for debugging and displaying state info.
     */
    public function __toString(): string
    {
        return $this->name . ($this->isAccepting ? ' (accepting)' : '');
    }

    /**
     * Compare states for equality based on name.
     * 
     * Note: Two states with the same name are considered equal,
     * even if they have different accepting status. This is because
     * in the FSM context, state names are unique identifiers.
     */
    public function equals(State $other): bool
    {
        return $this->name === $other->name;
    }
}
