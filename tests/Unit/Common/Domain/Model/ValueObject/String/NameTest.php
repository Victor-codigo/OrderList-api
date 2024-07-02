<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    private ValidationInterface $validation;

    private const string VALID_NAME = 'Juan_6';

    public function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    private function createName(string|null $name): Name
    {
        return new Name($name);
    }

    public function testValidName(): void
    {
        $name = $this->createName(self::VALID_NAME);
        $return = $this->validation->validateValueObject($name);

        $this->assertEmpty($return,
            'It was expected that return was empty');
    }

    public function testValidNameBlank(): void
    {
        $name = $this->createName('');
        $return = $this->validation->validateValueObject($name);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'It was expected that return contains [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT]');
    }

    public function testValidNameNull(): void
    {
        $name = $this->createName(null);
        $return = $this->validation->validateValueObject($name);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    public function testValidNameNotTooLong(): void
    {
        $name = $this->createName(str_repeat('f', 51));
        $return = $this->validation->validateValueObject($name);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return,
            'It was expected that return contains [VALIDATION_ERRORS::STRING_TOO_SHORT]');
    }

    public function testValidNameisAlphanumeric(): void
    {
        $name = $this->createName('anastasia-');
        $return = $this->validation->validateValueObject($name);

        $this->assertEquals([VALIDATION_ERRORS::ALPHANUMERIC], $return);
    }
}
