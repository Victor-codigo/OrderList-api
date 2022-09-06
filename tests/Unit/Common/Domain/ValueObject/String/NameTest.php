<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\IValidation;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\ValueObject\String\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    private IValidation $validation;

    private const VALID_NAME = 'Juan';

    public function setUp(): void
    {
        $this->validation = new ValidationChain();
    }

    private function createName(string $name): Name
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

    public function testValidNameNotTooLong(): void
    {
        $name = $this->createName(str_repeat('-', 51));
        $return = $this->validation->validateValueObject($name);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return,
            'It was expected that return contains [VALIDATION_ERRORS::STRING_TOO_SHORT]');
    }
}
