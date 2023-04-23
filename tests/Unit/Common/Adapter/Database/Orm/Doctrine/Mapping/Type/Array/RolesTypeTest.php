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
use PHPUnit\Framework\TestCase;

class RolesTypeTest extends TestCase
{
    private RolesType $object;
    private AbstractPlatform $abstractPlatform;

    public function setUp(): void
    {
        parent::setUp();

        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
        $this->object = new RolesType();
    }

    /** @test */
    public function convertToDatabaseValueReturnACorrectJson(): void
    {
        $roles = new Roles([new rol(USER_ROLES::ADMIN), new Rol(USER_ROLES::USER)]);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertEquals(json_encode([USER_ROLES::ADMIN, USER_ROLES::USER]), $return);
    }

    /** @test */
    public function convertToDatabaseValueEmptyValuesToNull(): void
    {
        $roles = new Roles([]);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertNull($return);
    }

    /** @test */
    public function convertToDatabaseValueThrowExceptionInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToDatabaseValue('', $this->abstractPlatform);
    }

    /** @test */
    public function convertToPHPValueValueIsNullAndReturnNull(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->abstractPlatform);

        $this->assertNull($return);
    }

    /** @test */
    public function convertToPHPValueIsArrayEmptyReturnsEmptyRoles(): void
    {
        $return = $this->object->convertToPHPValue(json_encode([]), $this->abstractPlatform);

        $this->assertInstanceOf(Roles::class, $return);
        $this->assertEmpty($return->getValue());
    }

    /** @test */
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

    /** @test */
    public function convertToPHPValueInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToPHPValue('{', $this->abstractPlatform);
    }

    /** @test */
    public function convertToPHPValueJsonMalformedExpectLogicException(): void
    {
        $this->expectException(LogicException::class);

        $this->object->convertToPHPValue(json_encode('{"Not valid JSON"'), $this->abstractPlatform);
    }
}
