<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordRemember\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto;

class UserPasswordRememberInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidateEmailIsCorrect(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailEmailIsNull(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            null,
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailEmailIsBlank(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            '',
            'http://www.domain.com'
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailEmailIsWrong(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'this is not an email',
            'http://www.domain.com');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    #[Test]
    public function itShouldFailUrlIsNull(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            null
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailUrlIsBlank(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            ''
        );
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailUrlIsWrong(): void
    {
        $userPasswordRememberDto = new UserPasswordRememberInputDto(
            'email@host.com',
            'www.domain.com');
        $return = $userPasswordRememberDto->validate($this->validator);

        $this->assertSame(['passwordRememberUrl' => [VALIDATION_ERRORS::URL]], $return);
    }
}
