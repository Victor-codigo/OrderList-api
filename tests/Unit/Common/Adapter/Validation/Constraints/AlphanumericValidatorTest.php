<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Constraints;

use Common\Adapter\Validation\Constraints\Alphanumeric\Alphanumeric;
use Common\Adapter\Validation\Constraints\Alphanumeric\AlphanumericValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AlphanumericValidatorTest extends TestCase
{
    private const PATTERN = '/^[a-zA-Z0-9_]+$/i';

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
    public function itShouldFailValueIsNotClassAlphanumericValidator(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate('', $this->constraint);
    }

    /** @test */
    public function itShouldValueIsNull(): void
    {
        $return = $this->object->validate(null, $this->alphanumeric);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldValueIsEmptyString(): void
    {
        $return = $this->object->validate('', $this->alphanumeric);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldValueCantBeConvertedToString(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate(new \stdClass(), $this->alphanumeric);
    }

    /** @test */
    public function itShouldValueIsStringAlphanumeric(): void
    {
        $this->alphanumeric->pattern = self::PATTERN;

        $this->expectNotToPerformAssertions();
        $this->object->validate('lola_hello22', $this->alphanumeric);
    }

    /** @test */
    public function itShouldValueIsStringNotAlphanumeric(): void
    {
        $this->alphanumeric->pattern = self::PATTERN;

        $this->expectException(\Error::class);
        $this->object->validate('hello.22', $this->alphanumeric);
    }
}
