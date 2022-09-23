<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationPositiveNegativeTest extends TestCase
{
    private ValidationInterface $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validatePositiveOk(): void
    {
        $return = $this->object
            ->setValue(1)
            ->positive()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validatePositiveError(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->positive()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validatePositiveOrZeroOk(): void
    {
        $return = $this->object
            ->setValue(0)
            ->positiveOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validatePositiveOrZeroError(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->positiveOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE_OR_ZERO], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateNegativeOk(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->negative()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateNegativeError(): void
    {
        $return = $this->object
            ->setValue(0)
            ->negative()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NEGATIVE], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateNegativeOrZeroOk(): void
    {
        $return = $this->object
            ->setValue(0)
            ->negativeOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateNegativeOrZeroError(): void
    {
        $return = $this->object
            ->setValue(1)
            ->negativeOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NEGATIVE_OR_ZERO], $return,
            'validate: It was expected to return an empty array');
    }
}
