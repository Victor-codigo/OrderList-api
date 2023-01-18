<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Group\Domain\Model\GROUP_ROLES;
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
    public function validationForUserRolesOk()
    {
        $this->object = $this->createRol(USER_ROLES::ADMIN);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationForGroupRolesOk()
    {
        $this->object = $this->createRol(GROUP_ROLES::ADMIN);
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
        $this->object = $this->createRol(new \stdClass());
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }

    /** @test */
    public function itShouldCreateARolFromStringForUsersRole(): void
    {
        $return = Rol::fromString(USER_ROLES::USER->value);

        $this->assertEquals(ValueObjectFactory::createRol(USER_ROLES::USER), $return);
    }

    /** @test */
    public function itShouldCreateARolFromStringForGroupRole(): void
    {
        $return = Rol::fromString(GROUP_ROLES::USER->value);

        $this->assertEquals(ValueObjectFactory::createRol(GROUP_ROLES::USER), $return);
    }

    /** @test */
    public function itShouldFailCreatingARolFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Rol::fromString('value');
    }

    private function createRol(object|null $rol): Rol
    {
        return new Rol($rol);
    }
}
