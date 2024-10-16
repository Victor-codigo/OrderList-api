<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Constraints;

use Common\Adapter\Validation\Constraints\AlphanumericWithWhiteSpace\AlphanumericWithWhiteSpace;
use Common\Adapter\Validation\Constraints\AlphanumericWithWhiteSpace\AlphanumericWithWhiteSpaceValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AlphanumericWithWhiteSpaceValidatorTest extends TestCase
{
    private const string PATTERN = '/^[A-Za-zÀ-ÿ0-9_\s]+$/i';

    private AlphanumericWithWhiteSpaceValidator $object;
    private MockObject&AlphanumericWithWhiteSpace $alphanumericWithWhiteSpace;
    private MockObject&Constraint $constraint;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->alphanumericWithWhiteSpace = $this->createMock(AlphanumericWithWhiteSpace::class);
        $this->constraint = $this->createMock(Constraint::class);
        $this->object = new AlphanumericWithWhiteSpaceValidator();
    }

    #[Test]
    public function itShouldFailValueIsNotClassAlphanumericWithWhitespace(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->object->validate('', $this->constraint);
    }

    #[Test]
    public function itShouldFailValueIsNull(): void
    {
        $return = $this->object->validate(null, $this->alphanumericWithWhiteSpace);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldFailValueIsEmptyString(): void
    {
        $return = $this->object->validate('', $this->alphanumericWithWhiteSpace);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldFailValueCantBeConvertedToString(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->object->validate(new \stdClass(), $this->alphanumericWithWhiteSpace);
    }

    #[Test]
    public function itShouldValueIsStringAlphanumericWithWhiteSpace(): void
    {
        $this->alphanumericWithWhiteSpace->pattern = self::PATTERN;

        $this->expectNotToPerformAssertions();
        $this->object->validate('lola_hello 22', $this->alphanumericWithWhiteSpace);
    }

    #[Test]
    public function itShouldValueIsStringAlphanumericWithWhiteSpaceAndAccents(): void
    {
        $this->alphanumericWithWhiteSpace->pattern = self::PATTERN;

        $this->expectNotToPerformAssertions();
        $this->object->validate('lolá_hëllò 22', $this->alphanumericWithWhiteSpace);
    }

    #[Test]
    public function itShouldValueIsStringNotAlphanumericWithWhiteSpace(): void
    {
        $this->alphanumericWithWhiteSpace->pattern = self::PATTERN;

        $this->expectException(\Error::class);
        $this->object->validate('hello.22', $this->alphanumericWithWhiteSpace);
    }
}
