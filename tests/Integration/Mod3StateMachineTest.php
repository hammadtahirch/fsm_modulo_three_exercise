<?php

declare(strict_types=1);

namespace Tests\Integration;

use FSM\Examples\Mod3StateMachine;
use FSM\Exceptions\InvalidTransitionException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the Mod3StateMachine implementation.
 *
 * These tests ensure that:
 * - The mod3 implementation correctly identifies numbers divisible by 3
 * - Edge cases are handled properly (empty strings, leading zeros)
 * - Invalid inputs are rejected with clear error messages
 * - The implementation matches mathematical expectations
 *
 * Test Strategy:
 * - Test known divisible by 3 numbers (0, 3, 6, 9, 12, 15, etc.)
 * - Test known not divisible by 3 numbers (1, 2, 4, 5, 7, 8, etc.)
 * - Test edge cases (empty string, leading zeros)
 * - Test invalid inputs
 * - Test a comprehensive range for thoroughness
 */
class Mod3StateMachineTest extends TestCase
{
    private Mod3StateMachine $machine;

    protected function setUp(): void
    {
        $this->machine = Mod3StateMachine::create();
    }

    /**
     * Test that 0 (empty string) is divisible by 3.
     * 
     * This one was a bit tricky - had to think about whether empty string
     * should be accepted. Mathematically, 0 % 3 = 0, so yes!
     * Also confirmed with the interviewer that this isn't an edge case.
     */
    public function test_empty_string_is_divisible_by_three(): void
    {
        $result = $this->machine->isDivisibleByThree('');

        $this->assertTrue($result, 'Empty string (0) should be divisible by 3');
    }

    /**
     * Test single bit values.
     */
    public function test_single_bit_values(): void
    {
        $this->assertTrue($this->machine->isDivisibleByThree('0'));   // 0
        $this->assertFalse($this->machine->isDivisibleByThree('1'));  // 1
    }

    /**
     * Test two-bit values.
     */
    public function test_two_bit_values(): void
    {
        $this->assertTrue($this->machine->isDivisibleByThree('00'));   // 0
        $this->assertFalse($this->machine->isDivisibleByThree('01'));  // 1
        $this->assertFalse($this->machine->isDivisibleByThree('10'));  // 2
        $this->assertTrue($this->machine->isDivisibleByThree('11'));   // 3
    }

    /**
     * Test numbers divisible by 3 from 0 to 30.
     */
    public function test_numbers_divisible_by_three_0_to_30(): void
    {
        $divisibleBy3 = [0, 3, 6, 9, 12, 15, 18, 21, 24, 27, 30];

        foreach ($divisibleBy3 as $number) {
            $binary = decbin($number);
            $result = $this->machine->isDivisibleByThree($binary);

            $this->assertTrue(
                $result,
                "Binary '{$binary}' (decimal {$number}) should be divisible by 3"
            );
        }
    }

    /**
     * Test numbers NOT divisible by 3 from 1 to 30.
     */
    public function test_numbers_not_divisible_by_three_1_to_30(): void
    {
        $notDivisibleBy3 = [1, 2, 4, 5, 7, 8, 10, 11, 13, 14, 16, 17, 19, 20, 22, 23, 25, 26, 28, 29];

        foreach ($notDivisibleBy3 as $number) {
            $binary = decbin($number);
            $result = $this->machine->isDivisibleByThree($binary);

            $this->assertFalse(
                $result,
                "Binary '{$binary}' (decimal {$number}) should NOT be divisible by 3 (remainder: " . ($number % 3) . ")"
            );
        }
    }

    /**
     * Test larger numbers divisible by 3.
     */
    public function test_larger_numbers_divisible_by_three(): void
    {
        $testCases = [
            33   => '100001',
            60   => '111100',
            99   => '1100011',
            120  => '1111000',
            255  => '11111111',
            300  => '100101100',
            999  => '1111100111',
        ];

        foreach ($testCases as $decimal => $binary) {
            $result = $this->machine->isDivisibleByThree($binary);

            $this->assertTrue(
                $result,
                "Binary '{$binary}' (decimal {$decimal}) should be divisible by 3"
            );
        }
    }

    /**
     * Test leading zeros are handled correctly.
     * Assumption: Leading zeros are valid (e.g., "0011" = 3 in decimal)
     */
    public function test_leading_zeros_are_handled_correctly(): void
    {
        // "0011" = 3 in decimal (divisible by 3)
        $this->assertTrue($this->machine->isDivisibleByThree('0011'));

        // "0001" = 1 in decimal (not divisible by 3)
        $this->assertFalse($this->machine->isDivisibleByThree('0001'));

        // "00110" = 6 in decimal (divisible by 3)
        $this->assertTrue($this->machine->isDivisibleByThree('00110'));

        // "000" = 0 (divisible by 3)
        $this->assertTrue($this->machine->isDivisibleByThree('000'));
    }

    /**
     * Test that invalid characters throw exceptions.
     */
    public function test_invalid_characters_throw_exception(): void
    {
        $this->expectException(InvalidTransitionException::class);
        $this->expectExceptionMessage("Invalid input symbol '2'");

        $this->machine->isDivisibleByThree('1012');
    }

    public function test_non_binary_characters_throw_exception(): void
    {
        $this->expectException(InvalidTransitionException::class);

        $this->machine->isDivisibleByThree('10a1');
    }

    /**
     * Test comprehensive range to ensure correctness.
     * This tests every number from 0 to 100.
     * 
     * I wanted to be really thorough here - testing all numbers in a range
     * gives me confidence the algorithm is solid, not just lucky on a few cases.
     */
    public function test_comprehensive_range_0_to_100(): void
    {
        for ($i = 0; $i <= 100; $i++) {
            $binary = decbin($i);
            $expected = ($i % 3 === 0);
            $result = $this->machine->isDivisibleByThree($binary);

            $this->assertEquals(
                $expected,
                $result,
                "Binary '{$binary}' (decimal {$i}) - Expected: " . 
                ($expected ? 'divisible' : 'not divisible') . 
                " by 3, Got: " . ($result ? 'divisible' : 'not divisible')
            );
        }
    }

    /**
     * Test specific examples from the documentation.
     */
    public function test_documented_examples(): void
    {
        // Examples from Mod3StateMachine class documentation
        $this->assertTrue($this->machine->isDivisibleByThree('0'));      // 0
        $this->assertTrue($this->machine->isDivisibleByThree('11'));     // 3
        $this->assertTrue($this->machine->isDivisibleByThree('110'));    // 6
        $this->assertTrue($this->machine->isDivisibleByThree('1001'));   // 9

        $this->assertFalse($this->machine->isDivisibleByThree('1'));     // 1
        $this->assertFalse($this->machine->isDivisibleByThree('10'));    // 2
    }

    /**
     * Test that the underlying state machine is accessible.
     */
    public function test_can_access_underlying_state_machine(): void
    {
        $stateMachine = $this->machine->getStateMachine();

        $this->assertCount(3, $stateMachine->getStates(), 'Should have 3 states (S0, S1, S2)');
        $this->assertCount(2, $stateMachine->getAlphabet(), 'Should have 2 symbols (0, 1)');
        $this->assertCount(6, $stateMachine->getTransitions(), 'Should have 6 transitions (3 states × 2 symbols)');
        $this->assertEquals('S0', $stateMachine->getInitialState()->getName());
        $this->assertCount(1, $stateMachine->getAcceptingStates(), 'Should have 1 accepting state (S0)');
    }

    /**
     * Test state machine configuration matches expected mod3 behavior.
     */
    public function test_state_machine_configuration(): void
    {
        $stateMachine = $this->machine->getStateMachine();

        // Check S0 is the only accepting state
        $acceptingStates = $stateMachine->getAcceptingStates();
        $this->assertCount(1, $acceptingStates);
        $this->assertEquals('S0', $acceptingStates[0]->getName());

        // Check initial state is S0
        $this->assertEquals('S0', $stateMachine->getInitialState()->getName());

        // Check alphabet
        $this->assertEquals(['0', '1'], $stateMachine->getAlphabet());
    }

    /**
     * Test consecutive processing doesn't affect results (machine resets properly).
     */
    public function test_consecutive_processing(): void
    {
        // Process multiple inputs in sequence
        $this->assertTrue($this->machine->isDivisibleByThree('11'));   // 3
        $this->assertFalse($this->machine->isDivisibleByThree('1'));   // 1
        $this->assertTrue($this->machine->isDivisibleByThree('110'));  // 6
        $this->assertFalse($this->machine->isDivisibleByThree('10'));  // 2
        $this->assertTrue($this->machine->isDivisibleByThree('1001')); // 9

        // All results should be independent of previous processing
    }

    /**
     * Test powers of 2 for interesting pattern.
     */
    public function test_powers_of_two(): void
    {
        $powersOf2 = [
            1 => '1',       // Not divisible
            2 => '10',      // Not divisible
            4 => '100',     // Not divisible
            8 => '1000',    // Not divisible
            16 => '10000',  // Not divisible
            32 => '100000', // Not divisible
            64 => '1000000', // Not divisible
        ];

        foreach ($powersOf2 as $decimal => $binary) {
            $expected = ($decimal % 3 === 0);
            $result = $this->machine->isDivisibleByThree($binary);

            $this->assertEquals(
                $expected,
                $result,
                "Power of 2: {$decimal} (binary '{$binary}')"
            );
        }
    }
}

