<?php

declare(strict_types=1);

namespace Tests\Unit;

use FSM\State;
use FSM\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Transition class.
 *
 * These tests ensure that:
 * - Transitions are created correctly
 * - Transition properties are accessible
 * - Transition matching works correctly
 * - String representation is correct
 */
class TransitionTest extends TestCase
{
    private State $fromState;
    private State $toState;

    protected function setUp(): void
    {
        $this->fromState = new State('S0');
        $this->toState = new State('S1');
    }

    public function test_can_create_transition(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);

        $this->assertEquals($this->fromState, $transition->getFromState());
        $this->assertEquals('0', $transition->getSymbol());
        $this->assertEquals($this->toState, $transition->getToState());
    }

    public function test_transition_matches_correct_state_and_symbol(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);

        $this->assertTrue($transition->matches($this->fromState, '0'));
    }

    public function test_transition_does_not_match_different_state(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);
        $differentState = new State('S2');

        $this->assertFalse($transition->matches($differentState, '0'));
    }

    public function test_transition_does_not_match_different_symbol(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);

        $this->assertFalse($transition->matches($this->fromState, '1'));
    }

    public function test_transition_does_not_match_different_state_and_symbol(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);
        $differentState = new State('S2');

        $this->assertFalse($transition->matches($differentState, '1'));
    }

    public function test_transition_string_representation(): void
    {
        $transition = new Transition($this->fromState, '0', $this->toState);

        $this->assertEquals('S0 --[0]--> S1', (string) $transition);
    }

    public function test_can_create_transition_with_various_symbols(): void
    {
        $symbols = ['0', '1', 'a', 'b', 'ε', ' ', '->'];

        foreach ($symbols as $symbol) {
            $transition = new Transition($this->fromState, $symbol, $this->toState);
            $this->assertEquals($symbol, $transition->getSymbol());
        }
    }

    public function test_transition_with_self_loop(): void
    {
        $state = new State('S0');
        $transition = new Transition($state, '0', $state);

        $this->assertTrue($transition->getFromState()->equals($transition->getToState()));
        $this->assertEquals('S0 --[0]--> S0', (string) $transition);
    }
}

