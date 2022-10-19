<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRegister\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;
use User\Application\UserRegister\Dto\UserRegisterInputDto;
use User\Domain\Model\USER_ROLES;

class UserRegisterDtoTest extends TestCase
{
    private const ACTIVATION_TOKEN = '95852be1-7df7-4e9f-82a9-ef4db6296050';
    private const REGISTRATION_KEY = '23db9ca1-1568-473e-8c23-c4613205cf36';
    private const ADMIN_EMAIL = 'admin@email.com';
    private const APP_NAME = 'App Name';
    private const URL_EMAIL_CONFIRMATION = 'www.domain.com/confirmation';

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
            [new Rol(USER_ROLES::USER)],
            self::ACTIVATION_TOKEN,
            self::REGISTRATION_KEY,
            self::ADMIN_EMAIL,
            self::APP_NAME,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationEmailWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host',
            'password',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::ACTIVATION_TOKEN,
            self::REGISTRATION_KEY,
            self::ADMIN_EMAIL,
            self::APP_NAME,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertSame(['email' => [VALIDATION_ERRORS::EMAIL]], $return);
    }

    /** @test */
    public function validationPasswordWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'short',
            'John',
            [new Rol(USER_ROLES::USER)],
            self::ACTIVATION_TOKEN,
            self::REGISTRATION_KEY,
            self::ADMIN_EMAIL,
            self::APP_NAME,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['password' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function validationNameWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'Ana',
            [new Rol(USER_ROLES::USER)],
            self::ACTIVATION_TOKEN,
            self::REGISTRATION_KEY,
            self::ADMIN_EMAIL,
            self::APP_NAME,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function validationRolesWrong()
    {
        $this->object = UserRegisterInputDto::create(
            'email@host.com',
            'password',
            'John',
            [],
            self::ACTIVATION_TOKEN,
            self::REGISTRATION_KEY,
            self::ADMIN_EMAIL,
            self::APP_NAME,
            self::URL_EMAIL_CONFIRMATION
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals(['roles' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
