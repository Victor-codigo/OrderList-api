<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String\NotificationTypeType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationTypeTypeTest extends TestCase
{
    private NotificationTypeType $object;
    private MockObject|AbstractPlatform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->object = new NotificationTypeType();
    }

    /** @test */
    public function itShouldConvertToDatabaseValue(): void
    {
        $value = ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::USER_REGISTERED);
        $return = $this->object->convertToDatabaseValue($value, $this->platform);

        $this->assertSame(NOTIFICATION_TYPE::USER_REGISTERED->value, $return);
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
        $value = NOTIFICATION_TYPE::USER_REGISTERED->value;
        $expects = ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::USER_REGISTERED);
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
