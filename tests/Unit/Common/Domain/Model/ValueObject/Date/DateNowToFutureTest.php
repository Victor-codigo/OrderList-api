<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Date;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class DateNowToFutureTest extends TestCase
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
        $dateTimeEarly = new \DateTime();
        $dateTimeEarly->setTimestamp($dateTimeEarly->getTimestamp() - 3600);
        $object = new DateNowToFuture($dateTimeEarly);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateDateIsNull(): void
    {
        $object = new DateNowToFuture(null);

        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailDateTimeIsEarlierThanAnHour(): void
    {
        $dateTimeEarly = new \DateTime();
        $dateTimeEarly->setTimestamp($dateTimeEarly->getTimestamp() - 3601);
        $object = new DateNowToFuture($dateTimeEarly);

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN_OR_EQUAL], $return);
    }
}
