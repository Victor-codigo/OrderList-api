<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRegister\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;
use User\Application\UserRegister\Dto\UserRegisterInputDto;
use User\Domain\Model\USER_ROLES;

class UserRegisterInputDtoTest extends TestCase
{
    private const REGISTRATION_KEY = '23db9ca1-1568-473e-8c23-c4613205cf36';
    private const URL_EMAIL_CONFIRMATION = 'http://www.domain.com/confirmation';

    private UserRegisterInputDto $object;
    private ValidationChain $validator;

    public function setup(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function validationWorks()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationEmailMissing()
    {
        $this->object = UserRegisterInputDto::create(
            null,
            'password',
            'John',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function validationEmailWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host',
            'password',
            'John',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    /** @test */
    public function validationPasswordMissing()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            null,
            'John',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function validationPasswordWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'short',
            'John',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function validationNameMissing()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            null,
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function validationNameWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            '',
            [new rol(USER_ROLES::USER)],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function validationRolesMissing()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            null,
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['roles' => [VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function validationRolesWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            [],
            self::URL_EMAIL_CONFIRMATION,
            self::REGISTRATION_KEY
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['roles' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
