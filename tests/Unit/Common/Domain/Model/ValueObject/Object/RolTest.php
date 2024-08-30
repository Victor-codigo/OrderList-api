<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\TestCase;

class RolTest extends TestCase
{
    private Rol $object;
    private ValidationChain $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function validationForUserRolesOk(): void
    {
        $this->object = $this->createRol(USER_ROLES::ADMIN);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function validationForGroupRolesOk(): void
    {
        $this->object = $this->createRol(GROUP_ROLES::ADMIN);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function checkNotBlankNotNull(): void
    {
        $this->object = $this->createRol(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function checkRolNotValid(): void
    {
        $this->object = $this->createRol(new \stdClass());
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }

    #[Test]
    public function itShouldCreateARolFromStringForUsersRole(): void
    {
        $return = Rol::fromString(USER_ROLES::USER->value);

        $this->assertEquals(ValueObjectFactory::createRol(USER_ROLES::USER), $return);
    }

    #[Test]
    public function itShouldCreateARolFromStringForGroupRole(): void
    {
        $return = Rol::fromString(GROUP_ROLES::USER->value);

        $this->assertEquals(ValueObjectFactory::createRol(GROUP_ROLES::USER), $return);
    }

    #[Test]
    public function itShouldFailCreatingARolFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Rol::fromString('value');
    }

    private function createRol(?object $rol): Rol
    {
        return new Rol($rol);
    }
}
