<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Float;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = ValueObjectFactory::createMoney(0);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateMoneyIsNull(): void
    {
        $object = ValueObjectFactory::createMoney(null);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailLessThanZero(): void
    {
        $object = ValueObjectFactory::createMoney(-0.1);

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE_OR_ZERO], $return);
    }
}
