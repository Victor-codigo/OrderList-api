<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String\GroupTypeType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupTypeTypeTest extends TestCase
{
    private GroupTypeType $object;
    private MockObject|AbstractPlatform $platform;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->object = new GroupTypeType();
    }

    /** @test */
    public function itShouldConvertToDatabaseValue(): void
    {
        $value = ValueObjectFactory::createGroupType(GROUP_TYPE::USER);
        $return = $this->object->convertToDatabaseValue($value, $this->platform);

        $this->assertSame(GROUP_TYPE::USER->value, $return);
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
        $value = GROUP_TYPE::USER->value;
        $expects = ValueObjectFactory::createGroupType(GROUP_TYPE::USER);
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
