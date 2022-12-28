<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserEmailChange\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserEmailChange\Dto\UserEmailChangeInputDto;

class UserEmailChangeInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $userEmail = '';
        $emailNew = 'new.email@host.com';
        $password = '123456';
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailEmailIsNull(): void
    {
        $userEmail = '';
        $emailNew = null;
        $password = '123456';
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEquals(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailEmailNotValid(): void
    {
        $userEmail = '';
        $emailNew = 'new.email@host';
        $password = '123456';
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEquals(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordIsNull(): void
    {
        $userEmail = '';
        $emailNew = 'new.email@host.com';
        $password = null;
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNIsTooShort(): void
    {
        $userEmail = '';
        $emailNew = 'new.email@host.com';
        $password = '12345';
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordIsTooLong(): void
    {
        $userEmail = '';
        $emailNew = 'new.email@host.com';
        $password = str_pad('', 51, 'j');
        $object = new UserEmailChangeInputDto($userEmail, $emailNew, $password);
        $return = $object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }
}
