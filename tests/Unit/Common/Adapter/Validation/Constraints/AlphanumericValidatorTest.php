<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Constraints;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\Constraints\Alphanumeric\Alphanumeric;
use Common\Adapter\Validation\Constraints\Alphanumeric\AlphanumericValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AlphanumericValidatorTest extends TestCase
{
    private const string PATTERN = '/^[A-Za-zÀ-ÿ0-9_]+$/i';

    private AlphanumericValidator $object;
    private MockObject|Alphanumeric $alphanumeric;
    private MockObject|Constraint $constraint;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->alphanumeric = $this->createMock(Alphanumeric::class);
        $this->constraint = $this->createMock(Constraint::class);
        $this->object = new AlphanumericValidator();
    }

    #[Test]
    public function itShouldFailValueIsNotClassAlphanumericValidator(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate('', $this->constraint);
    }

    #[Test]
    public function itShouldValueIsNull(): void
    {
        $return = $this->object->validate(null, $this->alphanumeric);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldValueIsEmptyString(): void
    {
        $return = $this->object->validate('', $this->alphanumeric);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldValueCantBeConvertedToString(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->object->validate(new \stdClass(), $this->alphanumeric);
    }

    #[Test]
    public function itShouldValueIsStringAlphanumeric(): void
    {
        $this->alphanumeric->pattern = self::PATTERN;

        $this->expectNotToPerformAssertions();
        $this->object->validate('lola_hello22', $this->alphanumeric);
    }

    #[Test]
    public function itShouldValidateStringAlphanumericWithAccents(): void
    {
        $this->alphanumeric->pattern = self::PATTERN;

        $this->expectNotToPerformAssertions();
        $this->object->validate('lolá_hëllò22', $this->alphanumeric);
    }

    #[Test]
    public function itShouldValueIsStringNotAlphanumeric(): void
    {
        $this->alphanumeric->pattern = self::PATTERN;

        $this->expectException(\Error::class);
        $this->object->validate('hello.22', $this->alphanumeric);
    }
}
