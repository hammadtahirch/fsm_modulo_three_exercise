<?php

declare(strict_types=1);

namespace FSM;

/**
 * Represents a transition between states in a finite state machine.
 *
 * A transition is defined by:
 * - The state we're transitioning FROM
 * - The input symbol that triggers the transition
 * - The state we're transitioning TO
 *
 * This class is immutable to ensure state machine consistency.
 *
 * @immutable
 */
class Transition
{
    /**
     * @param State $fromState The source state
     * @param string $symbol The input symbol that triggers this transition
     * @param State $toState The destination state
     */
    public function __construct(
        private readonly State $fromState,
        private readonly string $symbol,
        private readonly State $toState
    ) {
        // Nothing to validate here - any state and symbol combination is valid
        // The builder will handle validation at a higher level
    }

    /**
     * Get the source state of this transition.
     */
    public function getFromState(): State
    {
        return $this->fromState;
    }

    /**
     * Get the input symbol that triggers this transition.
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * Get the destination state of this transition.
     */
    public function getToState(): State
    {
        return $this->toState;
    }

    /**
     * Check if this transition applies to the given state and symbol.
     * 
     * Used by the StateMachine to find the right transition during execution.
     */
    public function matches(State $state, string $symbol): bool
    {
        return $this->fromState->equals($state) && $this->symbol === $symbol;
    }

    /**
     * String representation of the transition.
     * 
     * Useful for debugging and logging what's happening in the FSM.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s --[%s]--> %s",
            $this->fromState->getName(),
            $this->symbol,
            $this->toState->getName()
        );
    }
}
