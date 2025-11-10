<?php

declare(strict_types=1);

namespace FSM;

use FSM\Exceptions\InvalidStateException;
use FSM\Exceptions\InvalidTransitionException;

/**
 * Represents a Deterministic Finite Automaton (DFA).
 *
 * This class encapsulates a complete state machine with:
 * - A set of states
 * - An alphabet of valid input symbols
 * - A transition function
 * - An initial state
 * - A set of accepting states
 *
 * Once built, the state machine is immutable to ensure consistency.
 *
 * Key Design Decisions:
 * - DFA implementation: Each state-symbol pair has exactly one transition
 * - Immutable after construction: Prevents accidental modification
 * - Validates input: Ensures only valid symbols from alphabet are processed
 * - Clear error messages: Helps debug configuration issues
 *
 * @immutable
 */
class StateMachine
{
    /** @var State Current state during execution */
    private State $currentState;

    /** @var array<string, State> Map of state names to State objects */
    private array $stateMap = [];

    /**
     * @param array<State> $states All states in the machine
     * @param array<string> $alphabet Valid input symbols
     * @param array<Transition> $transitions State transition definitions
     * @param State $initialState Starting state
     * @param array<State> $acceptingStates States that indicate acceptance
     */
    public function __construct(
        private readonly array $states,
        private readonly array $alphabet,
        private readonly array $transitions,
        private readonly State $initialState,
        private readonly array $acceptingStates
    ) {
        // Build state map for quick lookup
        foreach ($this->states as $state) {
            $this->stateMap[$state->getName()] = $state;
        }

        // Set initial state
        $this->currentState = $this->initialState;
    }

    /**
     * Process an input string through the state machine.
     *
     * The machine starts in the initial state and processes each symbol
     * in the input string, transitioning between states according to
     * the defined transition function.
     *
     * Assumption: Empty strings are valid input and will be accepted
     * if the initial state is an accepting state.
     *
     * @param string $input The input string to process
     * @return bool True if the input is accepted (ends in accepting state), false otherwise
     * @throws InvalidTransitionException If input contains invalid symbols or no transition exists
     */
    public function process(string $input): bool
    {
        // Always start fresh from initial state
        $this->reset();

        // Edge case: empty string handling
        // Discussed this with the team - empty string represents 0 for mod3
        if ($input === '') {
            return $this->isInAcceptingState();
        }

        // Process each character one by one
        // Note: str_split works well for single-byte symbols
        $symbols = str_split($input);
        foreach ($symbols as $symbol) {
            $this->transition($symbol);
        }

        // Check if we ended up in an accepting state
        return $this->isInAcceptingState();
    }

    /**
     * Perform a single state transition based on input symbol.
     *
     * @throws InvalidTransitionException If symbol is invalid or no transition exists
     */
    private function transition(string $symbol): void
    {
        // First, make sure the symbol is valid for our alphabet
        if (!in_array($symbol, $this->alphabet, true)) {
            throw InvalidTransitionException::invalidSymbol($symbol, $this->alphabet);
        }

        // Look for the transition that matches current state and symbol
        // TODO: Could optimize this with a hash map if performance becomes an issue
        $nextState = null;
        foreach ($this->transitions as $transition) {
            if ($transition->matches($this->currentState, $symbol)) {
                $nextState = $transition->getToState();
                break; // Found it, no need to keep looking
            }
        }

        // This shouldn't happen if the builder validated properly, but check anyway
        if ($nextState === null) {
            throw InvalidTransitionException::notDefined($this->currentState->getName(), $symbol);
        }

        $this->currentState = $nextState;
    }

    /**
     * Check if the machine is currently in an accepting state.
     */
    private function isInAcceptingState(): bool
    {
        foreach ($this->acceptingStates as $acceptingState) {
            if ($this->currentState->equals($acceptingState)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reset the machine to its initial state.
     */
    public function reset(): void
    {
        $this->currentState = $this->initialState;
    }

    /**
     * Get the current state of the machine.
     */
    public function getCurrentState(): State
    {
        return $this->currentState;
    }

    /**
     * Get all states in the machine.
     *
     * @return array<State>
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * Get the alphabet of the machine.
     *
     * @return array<string>
     */
    public function getAlphabet(): array
    {
        return $this->alphabet;
    }

    /**
     * Get all transitions in the machine.
     *
     * @return array<Transition>
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * Get the initial state.
     */
    public function getInitialState(): State
    {
        return $this->initialState;
    }

    /**
     * Get all accepting states.
     *
     * @return array<State>
     */
    public function getAcceptingStates(): array
    {
        return $this->acceptingStates;
    }

    /**
     * Get a state by name.
     *
     * @throws InvalidStateException If state doesn't exist
     */
    public function getState(string $name): State
    {
        if (!isset($this->stateMap[$name])) {
            throw InvalidStateException::undefined($name);
        }
        return $this->stateMap[$name];
    }
}

