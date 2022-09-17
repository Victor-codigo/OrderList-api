<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures\CustomType;
use Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures\CustomValueObject;
use stdClass;

class TypeBaseTest extends TestCase
{
    private MockObject|TypeBase $object;
    private MockObject|AbstractPlatform $platform;

    public function setUp(): void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);

        $this->object = $this->createPartialMock(CustomType::class, ['getClassImplementationName']);
    }

    /** @test */
    public function convertToDatabaseValueValueIsNull(): void
    {
        $value = null;
        $return = $this->object->convertToDatabaseValue($value, $this->platform);

        $this->assertNull($return,
            'convertToDatabaseValue: It was expected to return null');
    }

    /** @test */
    public function convertToDatabaseValueValueIsAType(): void
    {
        $this->object
            ->expects($this->once())
            ->method('getClassImplementationName')
            ->willReturn(CustomValueObject::class);

        $value = new CustomValueObject(5);
        $return = $this->object->convertToDatabaseValue($value, $this->platform);

        $this->assertEquals($value->getValue(), $return,
            'convertToDatabaseValue: The return is not expected');
    }

    /** @test */
    public function convertToDatabaseValueValueIsNotCorrectType(): void
    {
        $this->object
            ->expects($this->once())
            ->method('getClassImplementationName')
            ->willReturn(stdClass::class);

        $this->expectException(InvalidArgumentException::class);

        $value = new CustomValueObject(5);
        $this->object->convertToDatabaseValue($value, $this->platform);
    }

    public function convertToPHPValueValueIsNull()
    {
        $value = null;
        $return = $this->object->convertToPHPValue($value, $this->platform);

        $this->assertNull($return,
            'convertToPHPValue: It was expected to return null');
    }

    public function convertToPHPValueValueIsValueObject()
    {
        $this->object
            ->expects($this->once())
            ->method('getClassImplementationName')
            ->willReturn(CustomValueObject::class);

        $value = 5;
        $return = $this->object->convertToPHPValue($value, $this->platform);

        $this->assertInstanceOf(CustomValueObject::class, $return,
            'convertToPHPValue: ValueObject class is wrong');
    }
}
