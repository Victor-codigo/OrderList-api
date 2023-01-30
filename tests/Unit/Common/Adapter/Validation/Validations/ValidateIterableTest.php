<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;

class ValidateIterableTest extends TestCase
{
    private ValidationChain $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateCountExactly(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->count(5)
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateCountExactlyZeroItems(): void
    {
        $array = [];

        $return = $this->validator
            ->setValue($array)
            ->count(0)
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailCountNotExactly(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->count(4)
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::ITERABLE_NOT_EQUAL], $return);
    }

    /** @test */
    public function itShouldValidateCountRangeMin(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->countRange(5, 10)
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateCountRangeMax(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->countRange(3, 5)
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailCountRangeMin(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->countRange(6, 10)
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::ITERABLE_TOO_FEW], $return);
    }

    /** @test */
    public function itShouldFailCountRangeMax(): void
    {
        $array = [1, 2, 3, 4, 5];

        $return = $this->validator
            ->setValue($array)
            ->countRange(2, 4)
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::ITERABLE_TOO_MANY], $return);
    }

    /** @test */
    public function itShouldValidateDividibleBy(): void
    {
        $array = [1, 2, 3, 4, 5, 6];

        $return = $this->validator
            ->setValue($array)
            ->countDivisibleBy(2)
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNotDivisibleBy(): void
    {
        $array = [1, 2, 3, 4, 5, 6];

        $return = $this->validator
            ->setValue($array)
            ->countDivisibleBy(4)
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::ITERABLE_DIVISIBLE_BY], $return);
    }
}
