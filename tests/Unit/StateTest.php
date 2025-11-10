<?php

declare(strict_types=1);

namespace Tests\Unit;

use FSM\Exceptions\InvalidStateException;
use FSM\State;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the State class.
 *
 * These tests ensure that:
 * - States are created correctly with valid names
 * - Accepting/non-accepting states work as expected
 * - Invalid state names are rejected
 * - State comparison works correctly
 */
class StateTest extends TestCase
{
    public function test_can_create_state_with_name(): void
    {
        $state = new State('S0');

        $this->assertEquals('S0', $state->getName());
    }

    public function test_state_is_not_accepting_by_default(): void
    {
        $state = new State('S0');

        $this->assertFalse($state->isAccepting());
    }

    public function test_can_create_accepting_state(): void
    {
        $state = new State('S0', isAccepting: true);

        $this->assertTrue($state->isAccepting());
    }

    public function test_can_create_non_accepting_state(): void
    {
        $state = new State('S0', isAccepting: false);

        $this->assertFalse($state->isAccepting());
    }

    public function test_throws_exception_for_empty_state_name(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('State name cannot be empty');

        new State('');
    }

    public function test_throws_exception_for_whitespace_only_state_name(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('State name cannot be empty');

        new State('   ');
    }

    public function test_state_string_representation(): void
    {
        $nonAcceptingState = new State('S0');
        $acceptingState = new State('S1', isAccepting: true);

        $this->assertEquals('S0', (string) $nonAcceptingState);
        $this->assertEquals('S1 (accepting)', (string) $acceptingState);
    }

    public function test_states_with_same_name_are_equal(): void
    {
        $state1 = new State('S0');
        $state2 = new State('S0');

        $this->assertTrue($state1->equals($state2));
        $this->assertTrue($state2->equals($state1));
    }

    public function test_states_with_different_names_are_not_equal(): void
    {
        $state1 = new State('S0');
        $state2 = new State('S1');

        $this->assertFalse($state1->equals($state2));
        $this->assertFalse($state2->equals($state1));
    }

    public function test_accepting_status_does_not_affect_equality(): void
    {
        // Two states with same name but different accepting status are still equal
        // because equality is based on name only
        $state1 = new State('S0', isAccepting: false);
        $state2 = new State('S0', isAccepting: true);

        $this->assertTrue($state1->equals($state2));
    }

    public function test_state_names_can_contain_various_characters(): void
    {
        $state1 = new State('State_0');
        $state2 = new State('state-1');
        $state3 = new State('STATE 2');
        $state4 = new State('q0');

        $this->assertEquals('State_0', $state1->getName());
        $this->assertEquals('state-1', $state2->getName());
        $this->assertEquals('STATE 2', $state3->getName());
        $this->assertEquals('q0', $state4->getName());
    }
}

