<?php

declare(strict_types=1);

namespace FSM\Examples;

use FSM\StateMachine;
use FSM\StateMachineBuilder;

/**
 * A Finite State Machine that determines if a binary number is divisible by 3.
 *
 * PROBLEM EXPLANATION:
 * -------------------
 * Given a binary string (e.g., "1101"), determine if the number it represents
 * is divisible by 3.
 *
 * MATHEMATICAL FOUNDATION:
 * -----------------------
 * When reading a binary number from left to right, we can track the remainder
 * when divided by 3. As we read each bit:
 *   - If current remainder is r and we read bit b, then:
 *     new_remainder = (r * 2 + b) % 3
 *
 * This gives us three states (representing remainders 0, 1, 2):
 *   - S0: remainder 0 (divisible by 3) - ACCEPTING STATE
 *   - S1: remainder 1
 *   - S2: remainder 2
 *
 * TRANSITION TABLE:
 * ----------------
 *   State | Input '0' | Input '1'
 *   ------|-----------|----------
 *    S0   |    S0     |    S1
 *    S1   |    S2     |    S0
 *    S2   |    S1     |    S2
 *
 * WORKED EXAMPLE - "110" (6 in decimal):
 * ---------------------------------------
 *   Start: S0 (remainder 0)
 *   Read '1': (0*2+1)%3 = 1 → Move to S1
 *   Read '1': (1*2+1)%3 = 0 → Move to S0  
 *   Read '0': (0*2+0)%3 = 0 → Stay at S0
 *   End at S0 → ACCEPT ✓
 *
 * MORE EXAMPLES:
 * -------------
 * - "0" (0 in decimal) → S0 → ACCEPT (0 % 3 = 0) ✓
 * - "11" (3 in decimal) → S0→S1→S0 → ACCEPT (3 % 3 = 0) ✓
 * - "1001" (9 in decimal) → ACCEPT (9 % 3 = 0) ✓
 * - "1" (1 in decimal) → S0→S1 → REJECT (1 % 3 = 1) ✗
 * - "10" (2 in decimal) → S0→S1→S2 → REJECT (2 % 3 = 2) ✗
 *
 * ASSUMPTIONS:
 * -----------
 * 1. Empty string "" represents 0, which is divisible by 3 → ACCEPT
 * 2. Leading zeros are valid (e.g., "0011" = 3 in decimal)
 * 3. Input must contain only '0' and '1' characters
 *
 * @see https://en.wikipedia.org/wiki/Finite-state_machine
 */
class Mod3StateMachine
{
    private StateMachine $machine;

    /**
     * Create a new Mod3 state machine instance.
     *
     * The machine is built using the generic StateMachineBuilder,
     * demonstrating how the framework can be used to solve specific problems.
     */
    public function __construct()
    {
        // Build the FSM on initialization
        // Could lazy-load this if we needed to, but for mod3 it's fast enough
        $this->machine = $this->buildMachine();
    }

    /**
     * Check if a binary string represents a number divisible by 3.
     *
     * @param string $binaryString Binary string to check (e.g., "1101")
     * @return bool True if divisible by 3, false otherwise
     */
    public function isDivisibleByThree(string $binaryString): bool
    {
        // Just delegate to the underlying state machine
        return $this->machine->process($binaryString);
    }

    /**
     * Get the underlying state machine (for inspection/testing).
     */
    public function getStateMachine(): StateMachine
    {
        return $this->machine;
    }

    /**
     * Build the mod3 state machine using the generic builder.
     *
     * This method demonstrates the use of the StateMachineBuilder
     * to create a specific FSM for the mod3 problem.
     * 
     * The math here is based on tracking remainders as we read bits left-to-right.
     * Formula: new_remainder = (old_remainder * 2 + bit) % 3
     * 
     * I worked through a few examples by hand to verify the transitions:
     * - "11" (3 in decimal): S0->S1->S0 ✓
     * - "110" (6 in decimal): S0->S1->S0->S0 ✓
     */
    private function buildMachine(): StateMachine
    {
        return StateMachineBuilder::create()
            // Binary alphabet - just 0 and 1
            ->withAlphabet(['0', '1'])

            // Three states for the three possible remainders (0, 1, 2)
            ->addState('S0', isAccepting: true)  // remainder 0 means divisible!
            ->addState('S1', isAccepting: false) // remainder 1
            ->addState('S2', isAccepting: false) // remainder 2

            // Start at S0 since we begin with remainder 0
            ->setInitialState('S0')

            // Now define all the transitions
            // I calculated these using (r*2 + b) % 3 for each combination
            
            // From S0 (remainder 0):
            ->addTransition('S0', '0', 'S0') // 0*2+0 = 0, 0%3 = 0
            ->addTransition('S0', '1', 'S1') // 0*2+1 = 1, 1%3 = 1

            // From S1 (remainder 1):
            ->addTransition('S1', '0', 'S2') // 1*2+0 = 2, 2%3 = 2
            ->addTransition('S1', '1', 'S0') // 1*2+1 = 3, 3%3 = 0 (back to accepting!)

            // From S2 (remainder 2):
            ->addTransition('S2', '0', 'S1') // 2*2+0 = 4, 4%3 = 1
            ->addTransition('S2', '1', 'S2') // 2*2+1 = 5, 5%3 = 2 (stay here)

            ->build();
    }

    /**
     * Factory method to create a new instance.
     */
    public static function create(): self
    {
        return new self();
    }
}

