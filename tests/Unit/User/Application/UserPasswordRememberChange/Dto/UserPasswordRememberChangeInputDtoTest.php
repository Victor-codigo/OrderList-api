<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordRememberChange\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto as DtoUserPasswordRememberChangeInputDto;

class UserPasswordRememberChangeInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateThePasswordNew(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '123456',
            '123456'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailPasswordNewIsNull(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            null,
            '123456'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNew' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewIsTooShort(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '12345',
            '123456'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNew' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewIsTooLong(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            str_pad('', 51, '-'),
            '123456'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNew' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsNull(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '123456',
            null
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNewRepeat' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsTooShort(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '123456',
            '12345'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNewRepeat' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsTooLong(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '123456',
            str_pad('', 51, '-'),
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['passwordNewRepeat' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldValidateTheToken(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 36, '-'),
            '123456',
            '123456'
        );

        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailTokenTooShort(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            str_pad('', 35, '-'),
            '123456',
            '123456'
        );

        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['token' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailTokenIsNull(): void
    {
        $userPasswordRememberChangeDto = new DtoUserPasswordRememberChangeInputDto(
            null,
            '123456',
            '123456'
        );
        $return = $userPasswordRememberChangeDto->validate($this->validator);

        $this->assertEquals(['token' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
