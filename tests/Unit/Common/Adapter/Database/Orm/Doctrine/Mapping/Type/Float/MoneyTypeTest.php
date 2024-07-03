<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float\MoneyType;
use Common\Domain\Model\ValueObject\Float\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class MoneyTypeTest extends TestCase
{
    private AbstractPlatform $abstractPlatform;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractPlatform = $this->createMock(AbstractPlatform::class);
    }

    /** @test */
    public function itShouldReturnAValidPhpValuePassedInt(): void
    {
        $value = 3;
        $object = new MoneyType($value);
        $return = $object->convertToPHPValue($value, $this->abstractPlatform);

        $this->assertInstanceOf(Money::class, $return);
        $this->assertEquals($value, $return->getValue());
    }

    /** @test */
    public function itShouldReturnAValidPhpValuePassedFloat(): void
    {
        $value = 3.2;
        $object = new MoneyType($value);
        $return = $object->convertToPHPValue($value, $this->abstractPlatform);

        $this->assertInstanceOf(Money::class, $return);
        $this->assertEquals($value, $return->getValue());
    }

    /** @test */
    public function itShouldReturnAValidPhpValuePassedString(): void
    {
        $value = '3.2';
        $object = new MoneyType($value);
        $return = $object->convertToPHPValue($value, $this->abstractPlatform);

        $this->assertInstanceOf(Money::class, $return);
        $this->assertEquals($value, $return->getValue());
    }

    /** @test */
    public function itShouldReturnAValidPhpValuePassedNull(): void
    {
        $value = null;
        $object = new MoneyType($value);
        $return = $object->convertToPHPValue($value, $this->abstractPlatform);

        $this->assertInstanceOf(Money::class, $return);
        $this->assertNull($return->getValue());
    }
}
