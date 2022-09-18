<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Rol;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;

class RolTest extends TestCase
{
    private Rol $object;
    private ValidationChain $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function validationOk()
    {
        $this->object = $this->createRol(USER_ROLES::ADMIN->value);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function checkNotBlankNotNull()
    {
        $this->object = $this->createRol(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    /** @test */
    public function checkRolNotValid()
    {
        $this->object = $this->createRol('Not valid rol');
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }

    private function createRol(string|null $rol): Rol
    {
        return new Rol($rol);
    }
}
