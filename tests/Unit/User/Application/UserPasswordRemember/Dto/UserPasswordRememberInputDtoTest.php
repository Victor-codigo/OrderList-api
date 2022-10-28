<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordRemember\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto as DtoUserPasswordRememberInputDto;

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
        $userPasswordRememberDto = new DtoUserPasswordRememberInputDto('email@host.com');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailEmailIsNull()
    {
        $userPasswordRememberDto = new DtoUserPasswordRememberInputDto(null);
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailEmailIsBlank()
    {
        $userPasswordRememberDto = new DtoUserPasswordRememberInputDto('');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailEmailIsWrong()
    {
        $userPasswordRememberDto = new DtoUserPasswordRememberInputDto('this is not an email');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }
}
