<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String\UnitMeasureType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UnitMeasureTypeTest extends TestCase
{
    private UnitMeasureType $object;
    private MockObject|AbstractPlatform $platform;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->object = new UnitMeasureType();
    }

    /** @test */
    public function itShouldConvertToDatabaseValue(): void
    {
        $value = ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS);
        $return = $this->object->convertToDatabaseValue($value, $this->platform);

        $this->assertSame(UNIT_MEASURE_TYPE::UNITS->value, $return);
    }

    /** @test */
    public function itShouldConvertNullValueToNullForDatabaseValue(): void
    {
        $return = $this->object->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldFailConvertingDataToDatabaseValueValueTypeIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $value = 'value';
        $this->object->convertToDatabaseValue($value, $this->platform);
    }

    /** @test */
    public function itShouldConvertToPhpValue(): void
    {
        $value = UNIT_MEASURE_TYPE::UNITS->value;
        $expects = ValueObjectFactory::createUnit(UNIT_MEASURE_TYPE::UNITS);
        $return = $this->object->convertToPHPValue($value, $this->platform);

        $this->assertEquals($expects, $return);
    }

    /** @test */
    public function itShouldFailConvertingToPhpValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $value = 'value';
        $this->object->convertToPHPValue($value, $this->platform);
    }
}
