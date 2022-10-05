<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\Validation\Fixtures\ValueObjectChildValueObjects;
use Test\Unit\Common\Adapter\Validation\Fixtures\ValueObjectForTesting;

class ValidatorTest extends TestCase
{
    private ValidationInterface $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    public function testGetValue()
    {
        $return = $this->object->getValue();

        $this->assertNull($return,
            'geValue: It was expected to return NULL');
    }

    /** @test */
    public function setValue()
    {
        $value = 33;
        $return = $this->object->setValue($value);

        $this->assertInstanceOf(ValidationChain::class, $return,
            'setValue: It was expected to return an instance of '.ValidationChain::class);

        $this->assertEquals($value, $this->object->getValue(),
            'setValue: The value passed is not the value set');
    }

    /** @test */
    public function validateOk()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate();

        $this->assertEmpty($return,
            'validate: It wasn\'t expected to return an error');
    }

    /** @test */
    public function validateOkAndNotRemoveConstraints()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate(false);

        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate(false);

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validate: It was expected to return an error: '.VALIDATION_ERRORS::EQUAL_TO->name);
    }

    /** @test */
    public function validateOkAndRemoveConstraints()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate();

        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate();

        $this->assertEmpty($return,
            'validate: It wasn\'t expected to return an error');
    }

    /** @test */
    public function validateError()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validate: It was expected to return an error: '.VALIDATION_ERRORS::EQUAL_TO->name);
    }

    /** @test */
    public function validateErrorAndNotRemoveConstraints()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate(false);

        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate(false);

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO, VALIDATION_ERRORS::EQUAL_TO], $return,
            'validate: It was expected to return an error: '.VALIDATION_ERRORS::EQUAL_TO->name);
    }

    /** @test */
    public function validateErrorAndRemoveConstraints()
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(10)
            ->validate();

        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate();

        $this->assertEmpty($return,
            'validate: It wasn\'t expected to return an error');
    }

    /** @test */
    public function validateValueObjectOk()
    {
        $valueObject = new ValueObjectForTesting(18);
        $return = $this->object->validateValueObject($valueObject);

        $this->assertEmpty($return,
            'validateValueObject: It was expected that return value is array empty');
    }

    /** @test */
    public function validateValueObjectError()
    {
        $valueObject = new ValueObjectForTesting(17);
        $return = $this->object->validateValueObject($valueObject);

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validateValueObject: It was expected that return value is '.VALIDATION_ERRORS::EQUAL_TO->name);
    }

    /** @test */
    public function validateValueObjectChildValueObjectsOk()
    {
        $valueObject = new ValueObjectChildValueObjects([
            new ValueObjectForTesting(18),
            new ValueObjectForTesting(18),
        ]);

        $return = $this->object->validateValueObject($valueObject);

        $this->assertEmpty($return,
            'validateValueObject: It was expected that return value is array empty');
    }

    /** @test */
    public function validateValueObjectChildValueObjectsError()
    {
        $valueObject = new ValueObjectChildValueObjects([
            new ValueObjectForTesting(10),
            new ValueObjectForTesting(18),
            new ValueObjectForTesting(11),
        ]);

        $return = $this->object->validateValueObject($valueObject);

        $expected = [
            'ValueObjectForTesting-1' => [VALIDATION_ERRORS::EQUAL_TO],
            'ValueObjectForTesting-3' => [VALIDATION_ERRORS::EQUAL_TO],
        ];

        $this->assertSame($expected, $return);
    }

    /** @test */
    public function validateValueObjectChildValueObjectsNestedError()
    {
        $valueObject = new ValueObjectChildValueObjects([
            new ValueObjectForTesting(10),
            new ValueObjectChildValueObjects([
                new ValueObjectForTesting(10),
                new ValueObjectForTesting(11),
                new ValueObjectForTesting(18),
            ]),
            new ValueObjectChildValueObjects([
                new ValueObjectForTesting(10),
                new ValueObjectForTesting(11),
            ]),
        ]);

        $return = $this->object->validateValueObject($valueObject);

        $expected = [
            'ValueObjectForTesting-1' => [VALIDATION_ERRORS::EQUAL_TO],
            'ValueObjectChildValueObjects-2' => [
                'ValueObjectForTesting-1' => [VALIDATION_ERRORS::EQUAL_TO],
                'ValueObjectForTesting-2' => [VALIDATION_ERRORS::EQUAL_TO],
            ],
            'ValueObjectChildValueObjects-3' => [
                'ValueObjectForTesting-1' => [VALIDATION_ERRORS::EQUAL_TO],
                'ValueObjectForTesting-2' => [VALIDATION_ERRORS::EQUAL_TO],
            ],
        ];

        $this->assertSame($expected, $return);
    }

    /** @test */
    public function validateValueObjectArrayOk()
    {
        $valueObjects = [
            new ValueObjectForTesting(18),
            new ValueObjectForTesting(18),
            new ValueObjectForTesting(18),
        ];
        $return = $this->object->validateValueObjectArray($valueObjects);

        $this->assertEmpty($return,
            'validateValueObjectArray: It wasn\'t expected errors');
    }

    /** @test */
    public function validateValueObjectArrayError()
    {
        $valueObjects = [
            new ValueObjectForTesting(18),
            new ValueObjectForTesting(50),
            new ValueObjectForTesting(18),
        ];
        $return = $this->object->validateValueObjectArray($valueObjects);

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validateValueObjectArray: It was expected that return value is '.VALIDATION_ERRORS::EQUAL_TO->name);
    }
}
