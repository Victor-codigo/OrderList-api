<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;
use User\Application\UserCreate\Dto\UserCreateInputDto;
use User\Domain\Model\USER_ROLES;

class UserCreateDtoTest extends TestCase
{
    private UserCreateInputDto $object;
    private ValidationChain $validator;

    public function setup(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function validationWorks()
    {
        $this->object = UserCreateInputDto::create(
            'email@host.com',
            'password',
            'John',
            [new Rol(USER_ROLES::USER)]
        );

        $return = $this->object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationEmailWrong()
    {
        $this->object = UserCreateInputDto::create(
            'email@host',
            'password',
            'John',
            [new Rol(USER_ROLES::USER)]
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::EMAIL], $return);
    }

    /** @test */
    public function validationPasswordWrong()
    {
        $this->object = UserCreateInputDto::create(
            'email@host.com',
            'short',
            'John',
            [new Rol(USER_ROLES::USER)]
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return);
    }

    /** @test */
    public function validationNameWrong()
    {
        $this->object = UserCreateInputDto::create(
            'email@host.com',
            'password',
            'Ana',
            [new Rol(USER_ROLES::USER)]
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return);
    }

    /** @test */
    public function validationRolesWrong()
    {
        $this->object = UserCreateInputDto::create(
            'email@host.com',
            'password',
            'John',
            []
        );

        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }
}
