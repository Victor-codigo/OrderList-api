<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    private ValidationInterface $validator;
    private string $validEmail = 'a.valid.email@host.com';
    private string $invalidEmail = 'this is an invalid email';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createEmail(string|null $email): Email
    {
        return new Email($email);
    }

    public function testEmailOk(): void
    {
        $email = $this->createEmail($this->validEmail);
        $return = $this->validator->validateValueObject($email);

        $this->assertEmpty($return,
            'It was expected that not errors returning');
    }

    public function testEmailNotBlank(): void
    {
        $email = $this->createEmail('');
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return,
            'It was expected that validation fail on notBlank');
    }

    public function testEmailNotNull(): void
    {
        $email = $this->createEmail(null);
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    public function testEmailNotValidEmail(): void
    {
        $email = $this->createEmail($this->invalidEmail);
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::EMAIL], $return,
            'It was expected that validation fail on email');
    }
}
