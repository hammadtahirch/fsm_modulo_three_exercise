<?php

declare(strict_types=1);

namespace Tests\Unit;

use FSM\Exceptions\InvalidConfigurationException;
use FSM\Exceptions\InvalidStateException;
use FSM\Exceptions\InvalidTransitionException;
use FSM\StateMachineBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the StateMachineBuilder class.
 *
 * These tests ensure that:
 * - Builder can construct valid state machines
 * - Invalid configurations are rejected
 * - Builder validates DFA properties
 * - Builder cannot be reused after building
 */
class StateMachineBuilderTest extends TestCase
{
    public function test_can_create_builder(): void
    {
        $builder = StateMachineBuilder::create();

        $this->assertInstanceOf(StateMachineBuilder::class, $builder);
    }

    public function test_can_build_simple_state_machine(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: true)
            ->addState('S1')
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0')
            ->addTransition('S0', '1', 'S1')
            ->addTransition('S1', '0', 'S1')
            ->addTransition('S1', '1', 'S0')
            ->build();

        $this->assertCount(2, $machine->getStates());
        $this->assertCount(2, $machine->getAlphabet());
        $this->assertCount(4, $machine->getTransitions());
    }

    public function test_throws_exception_when_no_states_defined(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must have at least one state');

        StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->build();
    }

    public function test_throws_exception_when_no_alphabet_defined(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must have at least one symbol');

        StateMachineBuilder::create()
            ->addState('S0')
            ->setInitialState('S0')
            ->build();
    }

    public function test_throws_exception_when_no_initial_state_set(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must have an initial state');

        StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0')
            ->build();
    }

    public function test_throws_exception_for_undefined_initial_state(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("State 'S999' is not defined");

        StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0')
            ->setInitialState('S999');
    }

    public function test_throws_exception_for_duplicate_state(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("State 'S0' already exists");

        StateMachineBuilder::create()
            ->addState('S0')
            ->addState('S0');
    }

    public function test_throws_exception_for_transition_from_undefined_state(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("State 'S999' is not defined");

        StateMachineBuilder::create()
            ->addState('S0')
            ->addTransition('S999', '0', 'S0');
    }

    public function test_throws_exception_for_transition_to_undefined_state(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("State 'S999' is not defined");

        StateMachineBuilder::create()
            ->addState('S0')
            ->addTransition('S0', '0', 'S999');
    }

    public function test_throws_exception_for_duplicate_transition(): void
    {
        $this->expectException(InvalidTransitionException::class);
        $this->expectExceptionMessage('Transition already exists');

        StateMachineBuilder::create()
            ->addState('S0')
            ->addState('S1')
            ->addTransition('S0', '0', 'S0')
            ->addTransition('S0', '0', 'S1'); // Duplicate!
    }

    public function test_throws_exception_for_incomplete_dfa(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("missing transitions for symbols: '1'");

        StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0')
            // Missing transition for '1' from S0
            ->build();
    }

    public function test_builder_fluent_interface(): void
    {
        $builder = StateMachineBuilder::create();

        $result = $builder->withAlphabet(['0']);
        $this->assertSame($builder, $result);

        $result = $builder->addState('S0');
        $this->assertSame($builder, $result);

        $result = $builder->setInitialState('S0');
        $this->assertSame($builder, $result);

        $result = $builder->addTransition('S0', '0', 'S0');
        $this->assertSame($builder, $result);
    }

    public function test_cannot_reuse_builder_after_building(): void
    {
        $builder = StateMachineBuilder::create()
            ->withAlphabet(['0'])
            ->addState('S0', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0');

        // First build should work
        $machine = $builder->build();
        $this->assertNotNull($machine);

        // Second build should fail
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('already been built');

        $builder->build();
    }

    public function test_cannot_modify_builder_after_building(): void
    {
        $builder = StateMachineBuilder::create()
            ->withAlphabet(['0'])
            ->addState('S0', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0');

        $builder->build();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('already been built');

        $builder->addState('S1');
    }

    public function test_can_build_machine_with_multiple_accepting_states(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0'])
            ->addState('S0', isAccepting: true)
            ->addState('S1', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S1')
            ->addTransition('S1', '0', 'S0')
            ->build();

        $this->assertCount(2, $machine->getAcceptingStates());
    }

    public function test_can_build_machine_with_no_accepting_states(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0'])
            ->addState('S0')
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0')
            ->build();

        $this->assertCount(0, $machine->getAcceptingStates());
    }

    public function test_validates_all_states_have_complete_transitions(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("State 'S1' is missing transitions");

        StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: true)
            ->addState('S1')
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S0')
            ->addTransition('S0', '1', 'S1')
            ->addTransition('S1', '0', 'S0')
            // Missing transition for '1' from S1
            ->build();
    }
}

