<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\IValidation;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;

class ValidationStringTest extends TestCase
{
    private IValidation $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validateStringLengthOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringLength(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringLengthError(): void
    {
        $return = $this->object
            ->setValue('123456')
            ->stringLength(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_NOT_EQUAL_LENGTH], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringMinOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringMin(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringMinError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->stringMin(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringMaxOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringMax(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringMaxError(): void
    {
        $return = $this->object
            ->setValue('123456')
            ->stringMax(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringRangeOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringRange(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateStringRangeError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->stringRange(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateUuIdOk(): void
    {
        $return = $this->object
            ->setValue('ea693dd6-670b-4b5e-b9fa-d324b7470afa')
            ->uuId()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateUuIdError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->uuId()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::UUID_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }
}
