<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Date;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Date\DateNowToFutureType;
use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateNowToFutureTypeTest extends TestCase
{
    private MockObject&AbstractPlatform $platform;
    private DateNowToFutureType $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->object = new DateNowToFutureType();
    }

    #[Test]
    public function itShouldConvertToPhpValueFromNullToNull(): void
    {
        $return = $this->object->convertToPHPValue(null, $this->platform);

        $this->assertInstanceOf(DateNowToFuture::class, $return);
        $this->assertNull($return->getValue());
    }

    #[Test]
    public function itShouldConvertToPhpValueFromDateToDateTime(): void
    {
        $dateTime = new \DateTime();
        $return = $this->object->convertToPHPValue(
            $dateTime->format('Y-m-d H:i:s'),
            $this->platform
        );

        $this->assertInstanceOf(DateNowToFuture::class, $return);
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $return->getValue()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function itShouldConvertToDatabaseValueFromNullToNull(): void
    {
        $dateNowToFuture = ValueObjectFactory::createDateNowToFuture(null);
        $return = $this->object->convertToDatabaseValue(
            $dateNowToFuture,
            $this->platform
        );

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldConvertToDatabaseValueFromDateToString(): void
    {
        $dateNowToFuture = ValueObjectFactory::createDateNowToFuture(new \DateTime());
        $return = $this->object->convertToDatabaseValue(
            $dateNowToFuture,
            $this->platform
        );

        $this->assertIsString($return);
        $this->assertEquals($dateNowToFuture->getValue()->format('Y-m-d H:i:s'), $return);
    }
}
