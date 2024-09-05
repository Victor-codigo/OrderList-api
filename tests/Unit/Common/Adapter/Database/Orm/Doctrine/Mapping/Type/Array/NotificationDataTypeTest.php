<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array\NotificationDataType;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationDataTypeTest extends TestCase
{
    private NotificationDataType $object;
    private AbstractPlatform $abstractPlatform;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->abstractPlatform = $this->createMock(AbstractPlatform::class);
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

    #[Test]
    public function convertToDatabaseValueReturnACorrectJson(): void
    {
        $notificationData = $this->getNotificationData();
        $roles = new NotificationData($notificationData);

        $return = $this->object->convertToDatabaseValue($roles, $this->abstractPlatform);

        $this->assertEquals(json_encode($notificationData), $return);
    }

    #[Test]
    public function convertToDatabaseValueEmptyValuesToNull(): void
    {
        $notificationData = new NotificationData([]);

        $return = $this->object->convertToDatabaseValue($notificationData, $this->abstractPlatform);

        $this->assertNull($return);
    }

    #[Test]
    public function convertToDatabaseValueThrowExceptionInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToDatabaseValue('', $this->abstractPlatform);
    }

    #[Test]
    public function convertToPHPValueValueIsNullAndReturnsArrayEmpty(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->abstractPlatform);

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertIsArray($return->getValue());
        $this->assertEmpty($return->getValue());
    }

    #[Test]
    public function convertToPHPValueIsArrayEmptyReturnsArrayEmpty(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->abstractPlatform);

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertIsArray($return->getValue());
        $this->assertEmpty($return->getValue());
    }

    #[Test]
    public function convertToPHPValueNotificationData(): void
    {
        $notificationData = $this->getNotificationData();
        $return = $this->object->convertToPHPValue(json_encode($notificationData), $this->abstractPlatform);

        $this->assertInstanceOf(NotificationData::class, $return);
        $this->assertSame($notificationData, $return->getValue());
    }

    #[Test]
    public function convertToPHPValueInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->convertToPHPValue('{', $this->abstractPlatform);
    }

    #[Test]
    public function convertToPHPValueJsonMalformedExpectLogicException(): void
    {
        $this->expectException(LogicException::class);

        $this->object->convertToPHPValue(json_encode('{"Not valid JSON"'), $this->abstractPlatform);
    }
}
