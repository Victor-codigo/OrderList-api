<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Constraints;

use Common\Adapter\Validation\Constraints\Alphanumeric\Alphanumeric;
use Common\Adapter\Validation\Constraints\Alphanumeric\AlphanumericValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use stdClass;

class AlphanumericValidatorTest extends TestCase
{
    private AlphanumericValidator $object;
    private MockObject|Alphanumeric $alphanumeric;
    private MockObject|Constraint $constraint;

    public function setUp(): void
    {
        parent::setUp();

        $this->alphanumeric = $this->createMock(Alphanumeric::class);
        $this->constraint = $this->createMock(Constraint::class);
        $this->object = new AlphanumericValidator();
    }

    /** @test */
    public function valueIsNotClassAlphanumericValidator(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate('', $this->constraint);
    }

    /** @test */
    public function valueIsNull(): void
    {
        $return = $this->object->validate(null, $this->alphanumeric);

        $this->assertNull($return);
    }

    /** @test */
    public function valueIsEmptyString(): void
    {
        $return = $this->object->validate('', $this->alphanumeric);

        $this->assertNull($return);
    }

    /** @test */
    public function valueCantBeComvertedToString(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate(new stdClass(), $this->alphanumeric);
    }
}
