<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class ValidationDateTimeTest extends TestCase
{
    private ValidationInterface $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function validateTimeZoneRestrictionAreaError(): void
    {
        $return = $this->object
            ->setValue('Europe/Madrid')
            ->timeZone(DateTimeZone::AFRICA)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::TIMEZONE_IDENTIFIER_IN_ZONE], $return,
            'validate: It was expected to return an empty array');
    }
}
