<?php

declare(strict_types=1);

/**
 * Example: Building Custom FSM Implementations
 *
 * This file demonstrates how to use the FSM framework to build
 * custom state machines for different problems.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FSM\StateMachineBuilder;

echo "=== Custom FSM Examples ===\n\n";

// Example 1: FSM that accepts strings with even number of 1s
echo "Example 1: Even Number of 1s\n";
echo str_repeat('-', 40) . "\n";

$evenOnesCounter = StateMachineBuilder::create()
    ->withAlphabet(['0', '1'])
    ->addState('Even', isAccepting: true)
    ->addState('Odd', isAccepting: false)
    ->setInitialState('Even')
    ->addTransition('Even', '0', 'Even')
    ->addTransition('Even', '1', 'Odd')
    ->addTransition('Odd', '0', 'Odd')
    ->addTransition('Odd', '1', 'Even')
    ->build();

$testStrings = ['1100', '111', '0000', '1'];
foreach ($testStrings as $str) {
    $result = $evenOnesCounter->process($str) ? 'ACCEPT' : 'REJECT';
    $count = substr_count($str, '1');
    echo "'{$str}' ({$count} ones) → {$result}\n";
}

echo "\n";

// Example 2: FSM that accepts strings ending with "01"
echo "Example 2: Strings Ending with '01'\n";
echo str_repeat('-', 40) . "\n";

$endsWithPattern = StateMachineBuilder::create()
    ->withAlphabet(['0', '1'])
    ->addState('Start', isAccepting: false)
    ->addState('Saw0', isAccepting: false)
    ->addState('Saw01', isAccepting: true)
    ->setInitialState('Start')
    ->addTransition('Start', '0', 'Saw0')
    ->addTransition('Start', '1', 'Start')
    ->addTransition('Saw0', '0', 'Saw0')
    ->addTransition('Saw0', '1', 'Saw01')
    ->addTransition('Saw01', '0', 'Saw0')
    ->addTransition('Saw01', '1', 'Start')
    ->build();

$testStrings = ['01', '101', '001', '1001', '10', '11'];
foreach ($testStrings as $str) {
    $result = $endsWithPattern->process($str) ? 'ACCEPT' : 'REJECT';
    echo "'{$str}' → {$result}\n";
}

echo "\n";

// Example 3: FSM that accepts strings with length multiple of 3
echo "Example 3: Length Multiple of 3\n";
echo str_repeat('-', 40) . "\n";

$lengthMod3 = StateMachineBuilder::create()
    ->withAlphabet(['a', 'b'])
    ->addState('Len0', isAccepting: true)   // Length % 3 = 0
    ->addState('Len1', isAccepting: false)  // Length % 3 = 1
    ->addState('Len2', isAccepting: false)  // Length % 3 = 2
    ->setInitialState('Len0')
    ->addTransition('Len0', 'a', 'Len1')
    ->addTransition('Len0', 'b', 'Len1')
    ->addTransition('Len1', 'a', 'Len2')
    ->addTransition('Len1', 'b', 'Len2')
    ->addTransition('Len2', 'a', 'Len0')
    ->addTransition('Len2', 'b', 'Len0')
    ->build();

$testStrings = ['', 'a', 'ab', 'aaa', 'abab', 'aabbaa'];
foreach ($testStrings as $str) {
    $result = $lengthMod3->process($str) ? 'ACCEPT' : 'REJECT';
    $len = strlen($str);
    echo "'{$str}' (length: {$len}) → {$result}\n";
}

echo "\n=== All examples completed! ===\n";

