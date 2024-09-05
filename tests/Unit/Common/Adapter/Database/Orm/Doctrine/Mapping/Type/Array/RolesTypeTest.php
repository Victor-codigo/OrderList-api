<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array\RolesType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\User\USER_ROLES;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RolesTypeTest extends TestCase
{
    private RolesType $object;
    private AbstractPlatform $abstractPlatform;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->abstractPlatform = $this->createMock(AbstractPlatform::class);
        $this->object = new RolesType();
    }

    #[Test]
    public function convertToDatabaseValueReturnACorrectJson(): void
    {
        $roles = new Roles([new Rol(USER_ROLES::ADMIN), new Rol(USER_ROLES::USER)]);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertEquals(json_encode([USER_ROLES::ADMIN, USER_ROLES::USER]), $return);
    }

    #[Test]
    public function convertToDatabaseValueEmptyValuesToNull(): void
    {
        $roles = new Roles([]);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertNull($return);
    }

    #[Test]
    public function convertToDatabaseValueThrowExceptionInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToDatabaseValue('', $this->abstractPlatform);
    }

    #[Test]
    public function convertToPHPValueValueIsNullAndReturnNull(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->abstractPlatform);

        $this->assertNull($return);
    }

    #[Test]
    public function convertToPHPValueIsArrayEmptyReturnsEmptyRoles(): void
    {
        $return = $this->object->convertToPHPValue(json_encode([]), $this->abstractPlatform);

        $this->assertInstanceOf(Roles::class, $return);
        $this->assertEmpty($return->getValue());
    }

    #[Test]
    public function convertToPHPValueManyRolesAndReturnRolesWithTheRoles(): void
    {
        $roles = [USER_ROLES::NOT_ACTIVE, USER_ROLES::USER];
        $return = $this->object->convertToPHPValue(json_encode($roles), $this->abstractPlatform);

        $this->assertInstanceOf(Roles::class, $return);

        $rolesReturned = [];

        foreach ($return->getValue() as $rol) {
            $rolesReturned[] = $rol->getValue();
        }

        $this->assertSame($roles, $rolesReturned);
    }

    #[Test]
    public function convertToPHPValueInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToPHPValue('{', $this->abstractPlatform);
    }

    #[Test]
    public function convertToPHPValueJsonMalformedExpectLogicException2589654(): void
    {
        $this->expectException(LogicException::class);

        $this->object->convertToPHPValue(json_encode('{"Not valid JSON"'), $this->abstractPlatform);
    }
}
