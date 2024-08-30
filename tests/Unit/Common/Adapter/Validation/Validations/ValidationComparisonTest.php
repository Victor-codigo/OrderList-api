<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationComparisonTest extends TestCase
{
    private ValidationInterface $object;

    #[\Override]
    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    #[Test]
    public function validateEqualToOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateEqualToError(): void
    {
        $return = $this->object
            ->setValue(6)
            ->equalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateNotEqualToOk(): void
    {
        $return = $this->object
            ->setValue(6)
            ->notEqualTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateNotEqualToError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->notEqualTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_EQUAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateIdenticalToOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->identicalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateIdenticalToError(): void
    {
        $return = $this->object
            ->setValue('5')
            ->identicalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::IDENTICAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateNotIdenticalToOk(): void
    {
        $return = $this->object
            ->setValue('5')
            ->notIdenticalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateNotIdenticalToError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->notIdenticalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_IDENTICAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateLessThanOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThan(6)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateLessThanError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::LESS_THAN], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateLessThanOrEqualOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateLessThanOrEqualError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThanOrEqual(4)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::LESS_THAN_OR_EQUAL], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateGreaterThanOk(): void
    {
        $return = $this->object
            ->setValue(6)
            ->greaterThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateGreaterThanError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->greaterThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateGreaterThanOrEqualOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->greaterThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateGreaterThanOrEqualError(): void
    {
        $return = $this->object
            ->setValue(4)
            ->greaterThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN_OR_EQUAL], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateRangeOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->range(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateRangeError(): void
    {
        $return = $this->object
            ->setValue(4)
            ->range(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::RANGE_NOT_IN_RANGE], $return,
            'validate: It was expected to return an empty array');
    }
}
