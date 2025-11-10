<?php

declare(strict_types=1);

namespace FSM;

use FSM\Exceptions\InvalidConfigurationException;
use FSM\Exceptions\InvalidStateException;
use FSM\Exceptions\InvalidTransitionException;

/**
 * Builder class for constructing StateMachine instances.
 *
 * This class provides a fluent interface for building state machines step by step.
 * It validates the configuration and ensures the resulting machine is a valid DFA.
 *
 * Key Design Decisions:
 * - Fluent Interface: Makes building intuitive and readable
 * - Validation: Ensures machine is properly configured before building
 * - DFA Enforcement: Validates completeness (all state-symbol pairs have transitions)
 * - Single Use: Once built, the builder cannot be reused (ensures immutability)
 *
 * Example Usage:
 * ```php
 * $machine = StateMachineBuilder::create()
 *     ->withAlphabet(['0', '1'])
 *     ->addState('S0', true)  // accepting state
 *     ->addState('S1')
 *     ->setInitialState('S0')
 *     ->addTransition('S0', '0', 'S0')
 *     ->addTransition('S0', '1', 'S1')
 *     ->build();
 * ```
 */
class StateMachineBuilder
{
    /** @var array<string, State> Map of state names to State objects */
    private array $states = [];

    /** @var array<string> Valid input symbols */
    private array $alphabet = [];

    /** @var array<Transition> State transitions */
    private array $transitions = [];

    /** @var State|null Initial state */
    private ?State $initialState = null;

    /** @var bool Whether this builder has been used to build a machine */
    private bool $built = false;

    /**
     * Private constructor to enforce use of static factory method.
     */
    private function __construct()
    {
    }

    /**
     * Create a new builder instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Define the alphabet (valid input symbols) for the state machine.
     *
     * @param array<string> $alphabet Array of valid input symbols
     * @return $this
     * @throws InvalidConfigurationException If builder has already been used
     */
    public function withAlphabet(array $alphabet): self
    {
        $this->ensureNotBuilt();
        $this->alphabet = array_values($alphabet); // Re-index array
        return $this;
    }

    /**
     * Add a state to the state machine.
     *
     * @param string $name Unique identifier for the state
     * @param bool $isAccepting Whether this is an accepting/final state
     * @return $this
     * @throws InvalidStateException If state already exists
     * @throws InvalidConfigurationException If builder has already been used
     */
    public function addState(string $name, bool $isAccepting = false): self
    {
        $this->ensureNotBuilt();

        if (isset($this->states[$name])) {
            throw InvalidStateException::duplicate($name);
        }

        $this->states[$name] = new State($name, $isAccepting);
        return $this;
    }

    /**
     * Set the initial state of the state machine.
     *
     * @param string $stateName Name of the state to use as initial state
     * @return $this
     * @throws InvalidStateException If state doesn't exist
     * @throws InvalidConfigurationException If builder has already been used
     */
    public function setInitialState(string $stateName): self
    {
        $this->ensureNotBuilt();
        $this->initialState = $this->getStateOrThrow($stateName);
        return $this;
    }

    /**
     * Add a transition between states.
     *
     * @param string $fromState Source state name
     * @param string $symbol Input symbol that triggers the transition
     * @param string $toState Destination state name
     * @return $this
     * @throws InvalidStateException If either state doesn't exist
     * @throws InvalidTransitionException If transition already exists (DFA constraint)
     * @throws InvalidConfigurationException If builder has already been used
     */
    public function addTransition(string $fromState, string $symbol, string $toState): self
    {
        $this->ensureNotBuilt();

        $from = $this->getStateOrThrow($fromState);
        $to = $this->getStateOrThrow($toState);

        // Check for duplicate transitions (DFA constraint)
        foreach ($this->transitions as $transition) {
            if ($transition->matches($from, $symbol)) {
                throw InvalidTransitionException::duplicate($fromState, $symbol);
            }
        }

        $this->transitions[] = new Transition($from, $symbol, $to);
        return $this;
    }

    /**
     * Build and return the configured state machine.
     *
     * This method validates the configuration and returns an immutable StateMachine.
     * After calling this, the builder cannot be reused.
     *
     * @return StateMachine The constructed state machine
     * @throws InvalidConfigurationException If configuration is invalid
     */
    public function build(): StateMachine
    {
        $this->ensureNotBuilt();
        $this->validate();

        // Mark as built to prevent reuse
        $this->built = true;

        $acceptingStates = array_filter(
            $this->states,
            fn(State $state) => $state->isAccepting()
        );

        return new StateMachine(
            states: array_values($this->states),
            alphabet: $this->alphabet,
            transitions: $this->transitions,
            initialState: $this->initialState,
            acceptingStates: array_values($acceptingStates)
        );
    }

    /**
     * Validate the state machine configuration.
     *
     * Ensures:
     * - At least one state exists
     * - Alphabet is not empty
     * - Initial state is set
     * - All transitions are complete (DFA requirement)
     *
     * @throws InvalidConfigurationException If configuration is invalid
     */
    private function validate(): void
    {
        // Basic sanity checks first
        if (empty($this->states)) {
            throw InvalidConfigurationException::noStates();
        }

        if (empty($this->alphabet)) {
            throw InvalidConfigurationException::emptyAlphabet();
        }

        if ($this->initialState === null) {
            throw InvalidConfigurationException::noInitialState();
        }

        // DFA completeness check - this is important!
        // Every state needs a transition for every symbol in the alphabet
        // Otherwise we could get stuck with undefined behavior
        foreach ($this->states as $state) {
            $definedSymbols = [];

            // Collect all symbols that have transitions from this state
            foreach ($this->transitions as $transition) {
                if ($transition->getFromState()->equals($state)) {
                    $definedSymbols[] = $transition->getSymbol();
                }
            }

            // See if we're missing any symbols
            $missingSymbols = array_diff($this->alphabet, $definedSymbols);
            if (!empty($missingSymbols)) {
                throw InvalidConfigurationException::incompleteTransitions(
                    $state->getName(),
                    $missingSymbols
                );
            }
        }
    }

    /**
     * Get a state by name or throw an exception if it doesn't exist.
     *
     * @throws InvalidStateException If state doesn't exist
     */
    private function getStateOrThrow(string $name): State
    {
        if (!isset($this->states[$name])) {
            throw InvalidStateException::undefined($name);
        }
        return $this->states[$name];
    }

    /**
     * Ensure the builder hasn't been used yet.
     *
     * @throws InvalidConfigurationException If builder has already been used
     */
    private function ensureNotBuilt(): void
    {
        if ($this->built) {
            throw InvalidConfigurationException::alreadyBuilt();
        }
    }
}

