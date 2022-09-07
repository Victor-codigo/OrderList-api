<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Port\Validation\Validations;

use Common\Adapter\Port\Validation\Validations\ValidationConstraint;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Range;

class ValidationConstraintTest extends TestCase
{
    private ValidationConstraint $object;
    private MockObject|Constraint $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = $this
            ->getMockBuilder(Constraint::class)
            ->disableOriginalConstructor()
            ->getMock();

        $idErrors = [
            Range::INVALID_CHARACTERS_ERROR => VALIDATION_ERRORS::RANGE_INVALID_CHARACTERS,
            Range::NOT_IN_RANGE_ERROR => VALIDATION_ERRORS::RANGE_NOT_IN_RANGE,
            Range::TOO_HIGH_ERROR => VALIDATION_ERRORS::RANGE_TOO_HIGH,
            Range::TOO_LOW_ERROR => VALIDATION_ERRORS::RANGE_TOO_LOW,
        ];

        if ($this->constraint instanceof Constraint) {
            $this->object = new ValidationConstraint($this->constraint, $idErrors);
        }
    }

    public function testHasErrorErrorDoesntExists(): void
    {
        $return = $this->object->hasError(Email::INVALID_FORMAT_ERROR);

        $this->assertFalse($return,
            'hasError: It was expected that the error is\'n going to be found');
    }

    public function testHasErrorErrorExists(): void
    {
        $return = $this->object->hasError(Range::TOO_HIGH_ERROR);

        $this->assertTrue($return,
            'hasError: It was expected that the error is going to be found');
    }
}
