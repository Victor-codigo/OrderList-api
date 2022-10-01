<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array\RolesType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\array\Roles;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;

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

        $return = $this->object->convertToDatabaseValue('', $this->abstractPlatform);
    }
}
