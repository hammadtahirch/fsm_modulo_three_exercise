# Finite State Machine Framework - Modulo 3 Implementation

A generic, extensible Finite State Machine (FSM) framework in PHP, with a complete implementation solving the modulo 3 problem: determining if a binary string represents a number divisible by 3.

[![Tests](https://img.shields.io/badge/tests-66%20passing-brightgreen)]()
[![Coverage](https://img.shields.io/badge/assertions-285-blue)]()
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4)]()

---

## 📋 Table of Contents

- [Overview](#overview)
- [The Modulo 3 Problem](#the-modulo-3-problem)
- [Architecture & Design Decisions](#architecture--design-decisions)
- [Key Assumptions & Reasoning](#key-assumptions--reasoning)
- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Extensibility](#extensibility)

---

## 🎯 Overview

This project implements a **generic Finite State Machine framework** that can be used to build any Deterministic Finite Automaton (DFA). It then demonstrates the framework's power by solving the classic modulo 3 problem: determining if a binary number is divisible by 3.

### What Makes This Implementation Strong

1. **Generic & Reusable**: The framework can solve ANY FSM problem, not just mod3
2. **Type-Safe**: Leverages PHP 8.1+ features (readonly properties, named arguments)
3. **Immutable**: State machines cannot be modified after construction (learned this the hard way in past projects!)
4. **Well-Tested**: 66 tests with 285 assertions covering unit and integration scenarios
5. **Clear Error Messages**: Comprehensive exceptions guide users when configuration is invalid
6. **Documented**: Inline documentation explaining design decisions and tradeoffs

---

## 🔢 The Modulo 3 Problem

### Problem Statement

Given a binary string (e.g., `"1101"`), determine if the number it represents is divisible by 3.

### Mathematical Foundation

When reading a binary number from left to right, we can track the remainder when divided by 3:

```
new_remainder = (old_remainder × 2 + current_bit) % 3
```

This mathematical property allows us to build a 3-state FSM:

- **S0**: Remainder 0 (divisible by 3) - **ACCEPTING STATE**
- **S1**: Remainder 1
- **S2**: Remainder 2

### State Transition Table

| State | Input '0' | Input '1' |
|-------|-----------|-----------|
| S0    | S0        | S1        |
| S1    | S2        | S0        |
| S2    | S1        | S2        |

### Examples

- `"11"` (3 in decimal) → S0→S1→S0 → **ACCEPT** ✓
- `"110"` (6 in decimal) → S0→S1→S0→S0 → **ACCEPT** ✓
- `"1001"` (9 in decimal) → **ACCEPT** (9 % 3 = 0) ✓
- `"10"` (2 in decimal) → S0→S1→S2 → **REJECT** (2 % 3 = 2) ✗

---

## 🏗️ Architecture & Design Decisions

### Core Components

```
src/
├── State.php                    # Represents individual states
├── Transition.php               # Represents state transitions
├── StateMachine.php             # The FSM engine (DFA implementation)
├── StateMachineBuilder.php      # Fluent builder for constructing FSMs
├── Exceptions/                  # Custom exceptions with clear messages
│   ├── InvalidStateException.php
│   ├── InvalidTransitionException.php
│   └── InvalidConfigurationException.php
└── Examples/
    └── Mod3StateMachine.php     # Mod3 problem implementation
```

### Design Pattern: Builder Pattern

**Why?** Constructing a valid FSM requires multiple steps with interdependencies. The Builder pattern:
- Provides a fluent, readable API
- Validates configuration before building
- Ensures immutability after construction
- Prevents invalid state machines from being created

### DFA vs NFA Choice

**Decision**: Implemented as a **Deterministic Finite Automaton (DFA)**

**Reasoning**:
- Simpler to implement and test
- Predictable behavior (one transition per state-symbol pair)
- Sufficient for the mod3 problem
- Validates completeness at build time
- Could be extended to NFA in future if needed

### Immutability

**Decision**: State machines are **immutable after construction**

**Reasoning**:
- Prevents accidental modification during execution
- Thread-safe (if PHP were to support true concurrency)
- Makes behavior predictable
- Simplifies testing and debugging
- Follows functional programming principles

### Error Handling Strategy

**Decision**: Use **custom exceptions** with descriptive messages

**Reasoning**:
- Helps developers quickly identify configuration issues
- Provides context (e.g., which state, which symbol)
- Better than silent failures or generic exceptions
- Aids in debugging during development

### Separation of Concerns

```
Framework Layer (Generic)
    ↓
Implementation Layer (Mod3 specific)
    ↓
Presentation Layer (Console command)
```

This separation ensures the framework can be reused for other FSM problems without modification.

---

## 🤔 Key Assumptions & Reasoning

### 1. Empty String Handling

**Assumption**: Empty string `""` represents 0, which is divisible by 3 → **ACCEPT**

**Reasoning**:
- Mathematical correctness: 0 % 3 = 0
- The interviewer clarified that empty strings are NOT an edge case (good to know!)
- An FSM accepts empty strings if the initial state is accepting
- Consistent with automata theory conventions
- I initially wasn't sure about this, but the math checks out

**Test Coverage**: ✅ Explicitly tested in `test_empty_string_is_divisible_by_three()`

### 2. Leading Zeros

**Assumption**: Leading zeros are **valid** (e.g., `"0011"` = 3 in decimal)

**Reasoning**:
- Binary numbers can have leading zeros (common in fixed-width representations)
- Mathematically equivalent: `0011` = `11` = 3
- Real-world systems (computers, protocols) use fixed-width binary
- The interviewer clarified this is not an edge case

**Test Coverage**: ✅ Explicitly tested in `test_leading_zeros_are_handled_correctly()`

### 3. Input Validation

**Assumption**: Validate input belongs to the defined alphabet

**Reasoning**:
- The interviewer emphasized that FSMs use inputs to transition between states
- Invalid symbols (e.g., '2' in binary) should be rejected clearly
- Provides immediate feedback to users
- Prevents undefined behavior

**Implementation**: Throws `InvalidTransitionException` with clear message

### 4. DFA Completeness Validation

**Assumption**: Every state must have a transition for every symbol in the alphabet

**Reasoning**:
- Required property of a DFA
- Prevents runtime errors from missing transitions
- Catches configuration mistakes at build time (fail-fast)
- Makes behavior predictable

**Implementation**: Builder validates completeness before allowing `build()`

### 5. State Machine Reset Behavior

**Assumption**: Machine resets to initial state before processing each input

**Reasoning**:
- Makes each `process()` call independent
- Prevents state pollution between invocations
- Simpler mental model for users
- Consistent with typical FSM usage patterns

**Alternative Considered**: Require manual reset, but this would complicate the API

### 6. Single Use Builder

**Assumption**: Builder cannot be reused after calling `build()`

**Reasoning**:
- Enforces immutability guarantee
- Prevents accidental modification of built machines
- Clear lifecycle: create builder → configure → build → done
- Encourages creating new builders for new machines

### 7. State Equality Based on Name

**Assumption**: Two states are equal if they have the same name

**Reasoning**:
- Name is the unique identifier
- Accepting status is a property, not part of identity
- Simplifies transition matching
- Consistent with how states are referenced in FSM theory

---

## 🚀 Installation

### Requirements

- PHP 8.1 or higher
- Composer

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd fsm_modulo_three_exercise

# Install dependencies
composer install

# Run tests to verify installation
composer test
```

---

## 💻 Usage

### Console Command

The project includes an interactive console command for testing the Mod3 FSM:

```bash
# Interactive mode
php bin/mod3

# Test a specific binary string
php bin/mod3 "1101"

# Run demo with test cases
php bin/mod3 --demo

# Show help
php bin/mod3 --help
```

### Programmatic Usage

#### Using the Mod3 Implementation

```php
use FSM\Examples\Mod3StateMachine;

$machine = Mod3StateMachine::create();

// Check if binary strings are divisible by 3
$machine->isDivisibleByThree('110');   // true (6 % 3 = 0)
$machine->isDivisibleByThree('1001');  // true (9 % 3 = 0)
$machine->isDivisibleByThree('101');   // false (5 % 3 = 2)
```

#### Building Your Own FSM

```php
use FSM\StateMachineBuilder;

$machine = StateMachineBuilder::create()
    ->withAlphabet(['0', '1'])
    ->addState('Even', isAccepting: true)  // Accepts even number of 1s
    ->addState('Odd', isAccepting: false)
    ->setInitialState('Even')
    ->addTransition('Even', '0', 'Even')
    ->addTransition('Even', '1', 'Odd')
    ->addTransition('Odd', '0', 'Odd')
    ->addTransition('Odd', '1', 'Even')
    ->build();

$machine->process('1100');  // true (two 1s - even)
$machine->process('111');   // false (three 1s - odd)
```

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with detailed output
php vendor/bin/phpunit --testdox

# Run specific test suite
php vendor/bin/phpunit tests/Unit
php vendor/bin/phpunit tests/Integration
```

### Test Coverage

The project includes **66 tests** with **285 assertions** covering:

#### Unit Tests (51 tests)
- ✅ **State**: Creation, validation, equality, string representation
- ✅ **Transition**: Creation, matching, various symbols
- ✅ **StateMachineBuilder**: Building, validation, error cases, fluent interface
- ✅ **StateMachine**: Processing, state management, error handling

#### Integration Tests (15 tests)
- ✅ **Mod3StateMachine**: Divisibility checks, edge cases, comprehensive range
- ✅ Tests every number from 0-100 for correctness
- ✅ Tests leading zeros, empty strings, invalid inputs
- ✅ Tests powers of 2, documented examples

### Testing Philosophy

Following the interviewer's emphasis on testing:

1. **Comprehensive Coverage**: Tests cover both happy paths and error cases
2. **Clear Test Names**: Each test clearly states what it validates
3. **Edge Cases**: Empty strings, leading zeros, invalid inputs all tested
4. **Integration Testing**: Mod3 implementation tested against 100+ numbers
5. **Fast Execution**: All tests run in ~11ms

---

## 📁 Project Structure

```
fsm_modulo_three_exercise/
├── bin/
│   └── mod3                          # Console command for testing
├── src/
│   ├── State.php                     # State representation
│   ├── Transition.php                # Transition representation
│   ├── StateMachine.php              # FSM engine
│   ├── StateMachineBuilder.php       # Builder for FSM construction
│   ├── Exceptions/                   # Custom exceptions
│   │   ├── InvalidStateException.php
│   │   ├── InvalidTransitionException.php
│   │   └── InvalidConfigurationException.php
│   └── Examples/
│       └── Mod3StateMachine.php      # Mod3 implementation
├── tests/
│   ├── Unit/                         # Unit tests for framework
│   │   ├── StateTest.php
│   │   ├── TransitionTest.php
│   │   ├── StateMachineBuilderTest.php
│   │   └── StateMachineTest.php
│   └── Integration/                  # Integration tests
│       └── Mod3StateMachineTest.php
├── composer.json                     # Dependencies and scripts
├── phpunit.xml                       # PHPUnit configuration
└── README.md                         # This file
```

---

## 🔧 Extensibility

The framework is designed to be extended for other FSM problems:

### Example: Building a Pattern Matcher

```php
// FSM that accepts strings containing "01" pattern
$machine = StateMachineBuilder::create()
    ->withAlphabet(['0', '1'])
    ->addState('Start', isAccepting: false)
    ->addState('Saw0', isAccepting: false)
    ->addState('Saw01', isAccepting: true)
    ->setInitialState('Start')
    ->addTransition('Start', '0', 'Saw0')
    ->addTransition('Start', '1', 'Start')
    ->addTransition('Saw0', '0', 'Saw0')
    ->addTransition('Saw0', '1', 'Saw01')
    ->addTransition('Saw01', '0', 'Saw01')
    ->addTransition('Saw01', '1', 'Saw01')
    ->build();
```

### Example: Building an Email Validator (Simplified)

```php
$machine = StateMachineBuilder::create()
    ->withAlphabet(['letter', '@', '.', 'letter'])
    ->addState('Start', isAccepting: false)
    ->addState('Username', isAccepting: false)
    ->addState('AtSign', isAccepting: false)
    ->addState('Domain', isAccepting: false)
    ->addState('Dot', isAccepting: false)
    ->addState('TLD', isAccepting: true)
    // ... define transitions
    ->build();
```

---

## 🎓 Learning & Iteration

### What I'd Improve With More Time

1. **Visualization**: Add method to export FSM as DOT format for Graphviz
2. **NFA Support**: Extend framework to support Non-deterministic Finite Automata
3. **Performance**: Benchmark and optimize for very long input strings
4. **More Examples**: Add more example implementations (pattern matching, lexers)
5. **Web Interface**: Build simple web UI for interactive testing

### Questions I Considered

1. **Should I support epsilon transitions?** 
   - No: DFA is sufficient, simpler to implement and test
   
2. **Should states be mutable?**
   - No: Immutability prevents bugs and simplifies reasoning

3. **Should I validate alphabet symbols?**
   - Yes: Clear error messages help users debug issues

4. **How should empty strings be handled?**
   - Accept if initial state is accepting (standard FSM behavior)
