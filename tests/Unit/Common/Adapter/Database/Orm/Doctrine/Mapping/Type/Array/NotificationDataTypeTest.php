<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array\NotificationDataType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class NotificationDataTypeTest extends TestCase
{
    private NotificationDataType $object;
    private AbstractPlatform $abstractPlatform;

    public function setUp(): void
    {
        parent::setUp();

        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
        $this->object = new NotificationDataType();
    }

    private function getNotificationData(): array
    {
        return [
            'data1' => 1,
            'data2' => 'data value 2',
            'data3' => [
                'data3-1' => 'data value 3-1',
                'data3-2' => 'data value 3-2',
                'data3-3' => [
                    'data3-3-1' => 456,
                    'data3-3-2' => 'data value 3-3-2',
                ],
                'data3-4',
            ],
        ];
    }

    /** @test */
    public function convertToDatabaseValueReturnACorrectJson(): void
    {
        $notificationData = $this->getNotificationData();
        $roles = new NotificationData($notificationData);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertEquals(json_encode($notificationData), $return);
    }

    /** @test */
    public function convertToDatabaseValueEmptyValuesToNull(): void
    {
        $notificationData = new NotificationData([]);

        $return = $this->object->convertToDatabaseValue($notificationData, $this->abstractPlatform);

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

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertNull($return->getValue());
    }

    /** @test */
    public function convertToPHPValueIsArrayEmptyReturnsNull(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->abstractPlatform);

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertNull($return->getValue());
    }

    /** @test */
    public function convertToPHPValueNotificationData(): void
    {
        $notificationData = $this->getNotificationData();
        $return = $this->object->convertToPHPValue(json_encode($notificationData), $this->abstractPlatform);

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertSame($notificationData, $return->getValue());
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
