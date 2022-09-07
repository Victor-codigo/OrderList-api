<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\ValueObjectBase;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\Fixtures\ValueObjectForTesting;

class ValueObjectTypeConverterTraitTest extends TestCase
{
    private AbstractPlatform $platform;

    public function setUp(): void
    {
        $this->platform = $this
            ->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createValueObject(int|null $value): ValueObjectForTesting
    {
        return new ValueObjectForTesting($value);
    }

    public function testConvertToPhpValueValueIsNull()
    {
        $valueObject = $this->createValueObject(null);
        $return = $valueObject->convertToPhpValue($valueObject->getValue(), $this->platform);

        $this->assertNull($return,
            'convertToPhpValue: It was expected the the return is NULL');
    }

    public function testConvertToPhpValueValueIsNotNull()
    {
        $valueObject = $this->createValueObject(5);
        $return = $valueObject->convertToPhpValue($valueObject->getValue(), $this->platform);

        $this->assertInstanceOf(ValueObjectForTesting::class, $return,
            'It was expected that returns '.ValueObjectForTesting::class);

        $this->assertEquals($valueObject->getValue(), $return->getValue(),
            'convertToPhpValue: Value passed to converter is not the same after the conversion');
    }

    public function testConvertToDatabaseValueValueIsNull()
    {
        $valueObject = $this->createValueObject(null);
        $return = $valueObject->convertToDatabaseValue($valueObject, $this->platform);

        $this->assertNull($return,
            'convertToDatabaseValue: It was expected the the return is NULL');
    }

    public function testConvertToDatabaseValueValueIsNotInstanceOfValueObjectForTesting()
    {
        $this->expectException(LogicException::class);

        /** @var MockObject|ValueObjectBase $valueObjectBase */
        $valueObjectBase = $this
            ->getMockBuilder(ValueObjectBase::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $valueObject = $this->createValueObject(5);
        $valueObject->convertToDatabaseValue($valueObjectBase, $this->platform);
    }

    public function testConvertToDatabaseValueReturningCorrectValueObject()
    {
        $valueObject = $this->createValueObject(5);
        $return = $valueObject->convertToDatabaseValue($valueObject, $this->platform);

        $this->assertIsInt($return,
            'convertToDatabaseValue: It was expected to return an integer');

        $this->assertEquals($valueObject->getValue(), $return,
            'convertToDatabaseValue: Doesn\'t return the expected value');
    }
}
