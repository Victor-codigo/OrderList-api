<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordRemember\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto;

class UserPasswordRememberInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateEmailIsCorrect()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailEmailIsNull()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            null,
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailEmailIsBlank()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            '',
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailEmailIsWrong()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'this is not an email',
            'http://www.domain.com');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    /** @test */
    public function itShouldFailUrlIsNull()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            null
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailUrlIsBlank()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            ''
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailUrlIsWrong()
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            'www.domain.com');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::URL]], $return);
    }
}
