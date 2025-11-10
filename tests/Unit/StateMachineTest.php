<?php

declare(strict_types=1);

namespace Tests\Unit;

use FSM\Exceptions\InvalidTransitionException;
use FSM\StateMachine;
use FSM\StateMachineBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the StateMachine class.
 *
 * These tests ensure that:
 * - State machines process input correctly
 * - Accept/reject decisions are correct
 * - Invalid inputs are handled properly
 * - Machine state can be reset
 * - Empty strings are handled per assumptions
 */
class StateMachineTest extends TestCase
{
    private StateMachine $simpleMachine;

    protected function setUp(): void
    {
        // Build a simple machine that accepts strings with even number of 1s
        $this->simpleMachine = StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('Even', isAccepting: true)  // Even number of 1s
            ->addState('Odd', isAccepting: false)  // Odd number of 1s
            ->setInitialState('Even')
            ->addTransition('Even', '0', 'Even')
            ->addTransition('Even', '1', 'Odd')
            ->addTransition('Odd', '0', 'Odd')
            ->addTransition('Odd', '1', 'Even')
            ->build();
    }

    public function test_machine_starts_in_initial_state(): void
    {
        $this->assertEquals('Even', $this->simpleMachine->getCurrentState()->getName());
    }

    public function test_can_process_accepting_input(): void
    {
        $result = $this->simpleMachine->process('11'); // Even number of 1s

        $this->assertTrue($result);
    }

    public function test_can_process_rejecting_input(): void
    {
        $result = $this->simpleMachine->process('1'); // Odd number of 1s

        $this->assertFalse($result);
    }

    public function test_empty_string_accepted_if_initial_state_is_accepting(): void
    {
        // Initial state 'Even' is accepting, so empty string should be accepted
        $result = $this->simpleMachine->process('');

        $this->assertTrue($result);
    }

    public function test_empty_string_rejected_if_initial_state_not_accepting(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: false)
            ->addState('S1', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S1')
            ->addTransition('S0', '1', 'S0')
            ->addTransition('S1', '0', 'S0')
            ->addTransition('S1', '1', 'S1')
            ->build();

        $result = $machine->process('');

        $this->assertFalse($result);
    }

    public function test_machine_resets_after_each_process(): void
    {
        $this->simpleMachine->process('1'); // Ends in 'Odd' state
        $result = $this->simpleMachine->process('11'); // Should start fresh in 'Even'

        $this->assertTrue($result);
    }

    public function test_can_manually_reset_machine(): void
    {
        $this->simpleMachine->process('1'); // Move to 'Odd' state

        $this->simpleMachine->reset();

        $this->assertEquals('Even', $this->simpleMachine->getCurrentState()->getName());
    }

    public function test_throws_exception_for_invalid_symbol(): void
    {
        $this->expectException(InvalidTransitionException::class);
        $this->expectExceptionMessage("Invalid input symbol '2'");

        $this->simpleMachine->process('012');
    }

    public function test_processes_transitions_correctly(): void
    {
        // Create a simple machine to verify transitions work correctly
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: true)
            ->addState('S1', isAccepting: false)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S1')
            ->addTransition('S0', '1', 'S0')
            ->addTransition('S1', '0', 'S0')
            ->addTransition('S1', '1', 'S1')
            ->build();

        // S0 --[0]--> S1 (not accepting)
        $this->assertFalse($machine->process('0'));
        
        // S0 --[0]--> S1 --[0]--> S0 (accepting)
        $this->assertTrue($machine->process('00'));
    }

    public function test_can_get_machine_properties(): void
    {
        $states = $this->simpleMachine->getStates();
        $alphabet = $this->simpleMachine->getAlphabet();
        $transitions = $this->simpleMachine->getTransitions();
        $initialState = $this->simpleMachine->getInitialState();
        $acceptingStates = $this->simpleMachine->getAcceptingStates();

        $this->assertCount(2, $states);
        $this->assertCount(2, $alphabet);
        $this->assertCount(4, $transitions);
        $this->assertEquals('Even', $initialState->getName());
        $this->assertCount(1, $acceptingStates);
    }

    public function test_can_get_state_by_name(): void
    {
        $state = $this->simpleMachine->getState('Even');

        $this->assertEquals('Even', $state->getName());
        $this->assertTrue($state->isAccepting());
    }

    public function test_machine_processes_complex_input(): void
    {
        // Test various inputs
        $this->assertTrue($this->simpleMachine->process('00'));     // 0 ones (even)
        $this->assertFalse($this->simpleMachine->process('01'));    // 1 one (odd)
        $this->assertTrue($this->simpleMachine->process('0011'));   // 2 ones (even)
        $this->assertFalse($this->simpleMachine->process('0111'));  // 3 ones (odd)
        $this->assertTrue($this->simpleMachine->process('1111'));   // 4 ones (even)
    }

    public function test_machine_with_single_state(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['a'])
            ->addState('S0', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', 'a', 'S0')  // Self-loop
            ->build();

        $this->assertTrue($machine->process(''));
        $this->assertTrue($machine->process('a'));
        $this->assertTrue($machine->process('aa'));
        $this->assertTrue($machine->process('aaa'));
    }

    public function test_machine_with_no_accepting_states(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['0', '1'])
            ->addState('S0', isAccepting: false)
            ->addState('S1', isAccepting: false)
            ->setInitialState('S0')
            ->addTransition('S0', '0', 'S1')
            ->addTransition('S0', '1', 'S0')
            ->addTransition('S1', '0', 'S0')
            ->addTransition('S1', '1', 'S1')
            ->build();

        // No input should be accepted
        $this->assertFalse($machine->process(''));
        $this->assertFalse($machine->process('0'));
        $this->assertFalse($machine->process('1'));
        $this->assertFalse($machine->process('01'));
    }

    public function test_alphabet_with_various_symbols(): void
    {
        $machine = StateMachineBuilder::create()
            ->withAlphabet(['a', 'b', 'c'])
            ->addState('S0', isAccepting: true)
            ->setInitialState('S0')
            ->addTransition('S0', 'a', 'S0')
            ->addTransition('S0', 'b', 'S0')
            ->addTransition('S0', 'c', 'S0')
            ->build();

        $this->assertTrue($machine->process('abc'));
        $this->assertTrue($machine->process('aabbcc'));
        $this->assertTrue($machine->process('cba'));
    }
}

