<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationDateTimeTest extends TestCase
{
    private ValidationInterface $object;

    #[\Override]
    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    #[Test]
    public function validateDateOk(): void
    {
        $return = $this->object
            ->setValue('2022-09-02')
            ->date()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateDateError(): void
    {
        $return = $this->object
            ->setValue('2022-9-2')
            ->date()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::DATE_INVALID_FORMAT], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateDateTimeOk(): void
    {
        $return = $this->object
            ->setValue('2022-09-02 5:20:00')
            ->dateTime()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateDateTimeError(): void
    {
        $return = $this->object
            ->setValue('2022-9-2')
            ->dateTime()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::DATETIME_INVALID_FORMAT], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateTimeOk(): void
    {
        $return = $this->object
            ->setValue('05:20:00')
            ->time()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateTimeError(): void
    {
        $return = $this->object
            ->setValue('5:20:00')
            ->time()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::TIME_INVALID_FORMAT], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateTimeZoneOk(): void
    {
        $return = $this->object
            ->setValue('Europe/Madrid')
            ->timeZone(null)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateTimeZoneError(): void
    {
        $return = $this->object
            ->setValue('5:20:00')
            ->timeZone(null)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::TIMEZONE_IDENTIFIER], $return,
            'validate: It was expected to return an empty array');
    }

    #[Test]
    public function validateTimeZoneRestrictionAreaError(): void
    {
        $return = $this->object
            ->setValue('Europe/Madrid')
            ->timeZone(\DateTimeZone::AFRICA)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::TIMEZONE_IDENTIFIER_IN_ZONE], $return,
            'validate: It was expected to return an empty array');
    }
}
