<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Port\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Validation\IValidation;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    private IValidation $validation;
    private string $validPassword = '123456';

    public function setUp(): void
    {
        $this->validation = new ValidationChain();
    }

    private function createPassword($password)
    {
        return new Password($password);
    }

    public function testPasswordOk()
    {
        $password = $this->createPassword($this->validPassword);
        $return = $this->validation->validateValueObject($password);

        $this->assertEmpty($return,
            'It was expected that doesnt return errors');
    }

    public function testPasswordNotBlankAndShort()
    {
        $password = $this->createPassword('');
        $return = $this->validation->validateValueObject($password);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'It was expected that returns errors: [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT]');
    }

    public function testPasswordNotTooLong()
    {
        $password = $this->createPassword(str_repeat('-', Password::PASSWORD_MAX_CHARS + 1));
        $return = $this->validation->validateValueObject($password);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return,
            'It was expected that returns errors: [VALIDATION_ERRORS::STRING_TOO_SHORT]');
    }
}
