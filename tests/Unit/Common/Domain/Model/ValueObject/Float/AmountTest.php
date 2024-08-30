<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Float;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class AmountTest extends TestCase
{
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = ValueObjectFactory::createAmount(0);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateAmountIsNull(): void
    {
        $object = ValueObjectFactory::createAmount(null);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailLessThanZero(): void
    {
        $object = ValueObjectFactory::createAmount(-0.1);

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE_OR_ZERO], $return);
    }
}
