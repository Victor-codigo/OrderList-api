<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\ValueObject\String;

use Common\Adapter\Validation\Validator;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\ValueObject\String\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    private Validator $validator;
    private string $validEmail = 'un.email.valido@host.com';
    private string $invalidEmail = 'this is an invalid email';

    public function setUp(): void
    {
        $this->validator = new Validator();
    }

    private function createEmail(string $email): Email
    {
        return new Email($email);
    }

    public function testEmailOk()
    {
        $email = $this->createEmail($this->validEmail);
        $return = $this->validator->validateValueObject($email);

        $this->assertEmpty($return,
            'It was expected that not errors returning');
    }

    public function testEmailNotBlank()
    {
        $email = $this->createEmail('');
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return,
            'It was expected that validation fail on notBlank');
    }

    public function testEmailNotValidEmail()
    {
        $email = $this->createEmail($this->invalidEmail);
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::EMAIL], $return,
            'It was expected that validation fail on email');
    }
}
