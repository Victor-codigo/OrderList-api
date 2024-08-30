<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Array;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\TestCase;

class RolesTest extends TestCase
{
    private Roles $object;
    private ValidationChain $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function isAValidRoles(): void
    {
        $this->object = $this->createRoles([USER_ROLES::USER, USER_ROLES::ADMIN]);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function noRolesError(): void
    {
        $this->object = $this->createRoles([]);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    #[Test]
    public function checkNotNullAndNotBlank(): void
    {
        $this->object = $this->createRoles(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    #[Test]
    public function checkIfRolesHasRol(): void
    {
        $this->object = $this->createRoles([USER_ROLES::ADMIN, USER_ROLES::USER]);
        $return = $this->object->has(new Rol(USER_ROLES::USER));

        $this->assertTrue($return);
    }

    #[Test]
    public function checkIfRolesHasNotRol(): void
    {
        $this->object = $this->createRoles([USER_ROLES::ADMIN, USER_ROLES::USER]);
        $return = $this->object->has(new Rol(USER_ROLES::NOT_ACTIVE));

        $this->assertFalse($return);
    }

    #[Test]
    public function itShouldReturnRolesEnumEmptyNoRoles(): void
    {
        $this->object = $this->createRoles(null);
        $return = $this->object->getRolesEnums();

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldReturnRolesEnum(): void
    {
        $roles = [USER_ROLES::ADMIN, USER_ROLES::USER];
        $this->object = $this->createRoles($roles);
        $return = $this->object->getRolesEnums();

        $this->assertSame($roles, $return);
    }

    private function createRoles(?array $roles): Roles
    {
        $rolesValueObject = [];

        if (null !== $roles) {
            foreach ($roles as $rol) {
                $rolesValueObject[] = new Rol($rol);
            }
        }

        return new Roles($rolesValueObject);
    }
}
