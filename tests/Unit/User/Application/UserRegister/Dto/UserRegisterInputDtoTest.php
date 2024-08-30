<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRegister\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\TestCase;
use User\Application\UserRegister\Dto\UserRegisterInputDto;

class UserRegisterInputDtoTest extends TestCase
{
    private const string URL_EMAIL_CONFIRMATION = 'http://www.domain.com/confirmation';

    private UserRegisterInputDto $object;
    private ValidationChain $validator;

    #[\Override]
    public function setup(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function validationWorks(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function validationEmailMissing(): void
    {
        $this->object = UserRegisterInputDto::create(
            null,
            'password',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function validationEmailWrong(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host',
            'password',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    #[Test]
    public function validationPasswordMissing(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            null,
            'John',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function validationPasswordWrong(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'short',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    #[Test]
    public function validationNameMissing(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            null,
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function validationNameWrong(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            '',
            [new Rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    #[Test]
    public function validationRolesMissing(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            null,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['roles' => [VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function validationRolesWrong(): void
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            [],
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['roles' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
