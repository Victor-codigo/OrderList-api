<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Array;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Rol;
use Common\Domain\Model\ValueObject\array\Roles;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;

class RolesTest extends TestCase
{
    private Roles $object;
    private ValidationChain $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function isAValidRoles(): void
    {
        $this->object = $this->createRoles([USER_ROLES::USER, USER_ROLES::ADMIN]);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function noRolesError(): void
    {
        $this->object = $this->createRoles([]);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    /** @test */
    public function ccheckNotNullAndNotBlank(): void
    {
        $this->object = $this->createRoles(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    private function createRoles(array|null $roles): Roles
    {
        $rolesValueObject = $roles;

        if (null !== $roles) {
            foreach ($roles as $rol) {
                $rolesValueObject[] = new Rol($rol->value);
            }
        }

        return new Roles($rolesValueObject);
    }
}
