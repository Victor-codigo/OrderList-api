<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidateChoiceTest extends TestCase
{
    private ValidationInterface $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validateChoiceOk(): void
    {
        $return = $this->object
            ->setValue('b')
            ->choice(['a', 'b', 'c'], false, true, 1, 1)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    /** @test */
    public function validateChoiceError(): void
    {
        $return = $this->object
            ->setValue('d')
            ->choice(['a', 'b', 'c'], false, true, 1, 1)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return,
            'validate: It was expected to return an empty array');
    }
}
