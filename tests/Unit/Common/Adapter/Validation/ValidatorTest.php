<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation;

use App\Common\Adapter\Validation\Validator;
use App\Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorTest extends TestCase
{
    private MockObject|ValidatorInterface $validator;
    private MockObject|ConstraintViolationListInterface $constraintsList;
    private Validator $object;

    public function setUp(): void
    {
        $this->validator = $this
            ->getMockBuilder(ValidatorInterface::class)
            ->getMock();

        $this->constraintsList = $this
            ->getMockBuilder(ConstraintViolationListInterface::class)
            ->getMockForAbstractClass();

        $this->object = new Validator();
    }

    public function testGetConstraintsCheckReturn()
    {
        $this->object->notBlank();
        $return = $this->object->getConstraints();

        $this->assertEquals([new NotBlank()], $return,
            'getConstraints: Returned value not expected');
    }

    public function testNotBlankConstraintReturnAndAddInConstraintsArray(): void
    {
        $return = $this->object->notBlank();
        $constraints = $this->object->getConstraints();

        $this->assertInstanceOf(Validator::class, $return,
            'notBlank: is not returning class '.Validator::class);

        $this->assertEquals([new NotBlank()], $constraints,
            "notBlank: Doesn't add constraint to array of constraints");
    }

    // public function testValidateValueIsBlank(): void
    // {
    //     $valueToCheck = '';
    //     $return = $this->object->validate($valueToCheck);

    //     $this->validator
    //         ->expects($this->once())
    //         ->method('validate')
    //         ->with($valueToCheck)
    //         ->willReturn($this->constraintsList);

    //     $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return,
    //         'validation: Returning value not expected');
    // }
}
