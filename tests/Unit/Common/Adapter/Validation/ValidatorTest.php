<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\EMAIL_TYPES;
use Common\Domain\Validation\IValidation;
use Common\Domain\Validation\TYPES;
use Common\Domain\Validation\VALIDATION_ERRORS;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\Validation\Fixtures\ValueObjectForTesting;

class ValidatorTest extends TestCase
{
    private IValidation $object;

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

    public function testSetValue()
    {
        $value = 33;
        $return = $this->object->setValue($value);

        $this->assertInstanceOf(ValidationChain::class, $return,
            'setValue: It was expected to return an instance of '.ValidationChain::class);

        $this->assertEquals($value, $this->object->getValue(),
            'setValue: The value passed is not the value set');
    }

    public function testValidateValueObject()
    {
        $valueObject = new ValueObjectForTesting(18);
        $return = $this->object->validateValueObject($valueObject);

        $this->assertEmpty($return,
            'validateValueObject: It was expected that return value is array empty');
    }

    public function testValidateValueObjectError()
    {
        $valueObject = new ValueObjectForTesting(17);
        $return = $this->object->validateValueObject($valueObject);

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validateValueObject: It was expected that return value is '.VALIDATION_ERRORS::EQUAL_TO->name);
    }

    public function testValidateCheckIfRemovesConstraints(): void
    {
        $return = $this->object
            ->setValue('lola')
            ->notBlank()
            ->validate(true);

        $return = $this->object
            ->setValue('lola')
            ->equalTo('lola')
            ->validate(true);

        $this->assertEmpty($return,
            'validate: It was expected not errors');
    }

    public function testValidateCheckIfNotRemovesConstraints(): void
    {
        $return = $this->object
            ->setValue('Marcos')
            ->equalTo('Marcos')
            ->validate(false);

        $return = $this->object
            ->setValue('lola')
            ->notBlank()
            ->validate(false);

        $this->assertNotEmpty($return,
            'validate: It should return errors');
    }

    public function testValidateNotBlankOk(): void
    {
        $return = $this->object
            ->setValue('lola')
            ->notBlank()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotBlankError(): void
    {
        $return = $this->object
            ->setValue('')
            ->notBlank()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotNullOk(): void
    {
        $return = $this->object
            ->setValue('lola')
            ->notNull()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotNullError(): void
    {
        $return = $this->object
            ->setValue(null)
            ->notNull()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateEmailOk(): void
    {
        $return = $this->object
            ->setValue('esto.es.un.email@email.com')
            ->email(EMAIL_TYPES::HTML5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateEmailError(): void
    {
        $return = $this->object
            ->setValue('asdf')
            ->email(EMAIL_TYPES::HTML5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::EMAIL], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateTypeOk(): void
    {
        $return = $this->object
            ->setValue(true)
            ->type(TYPES::BOOL)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateTypeError(): void
    {
        $return = $this->object
            ->setValue('asdf')
            ->type(TYPES::BOOL)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::TYPE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateUniqueOk(): void
    {
        $return = $this->object
            ->setValue(['a', 'b', 'c'])
            ->unique()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateUniqueError(): void
    {
        $return = $this->object
            ->setValue(['a', 'b', 'a'])
            ->unique()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::UNIQUE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateEqualToOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->equalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateEqualToError(): void
    {
        $return = $this->object
            ->setValue(6)
            ->equalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::EQUAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotEqualToOk(): void
    {
        $return = $this->object
            ->setValue(6)
            ->notEqualTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotEqualToError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->notEqualTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_EQUAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateIdenticalToOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->identicalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateIdenticalToError(): void
    {
        $return = $this->object
            ->setValue('5')
            ->identicalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::IDENTICAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotIdenticalToOk(): void
    {
        $return = $this->object
            ->setValue('5')
            ->notIdenticalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNotIdenticalToError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->notIdenticalTo(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NOT_IDENTICAL_TO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateLessThanOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThan(6)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateLessThanError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::LESS_THAN], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateLessThanOrEqualOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateLessThanOrEqualError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->lessThanOrEqual(4)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::LESS_THAN_OR_EQUAL], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateGreaterThanOk(): void
    {
        $return = $this->object
            ->setValue(6)
            ->greaterThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateGreaterThanError(): void
    {
        $return = $this->object
            ->setValue(5)
            ->greaterThan(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateGreaterThanOrEqualOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->greaterThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateGreaterThanOrEqualError(): void
    {
        $return = $this->object
            ->setValue(4)
            ->greaterThanOrEqual(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN_OR_EQUAL], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateRangeOk(): void
    {
        $return = $this->object
            ->setValue(5)
            ->range(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateRangeError(): void
    {
        $return = $this->object
            ->setValue(4)
            ->range(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::RANGE_NOT_IN_RANGE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateDateOk(): void
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

    public function testValidateDateError(): void
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

    public function testValidateDateTimeOk(): void
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

    public function testValidateDateTimeError(): void
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

    public function testValidateTimeOk(): void
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

    public function testValidateTimeError(): void
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

    public function testValidateTimeZoneOk(): void
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

    public function testValidateTimeZoneError(): void
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

    public function testValidateTimeZoneRestrictionAreaError(): void
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

    public function testValidateFileOk(): void
    {
        $return = $this->object
            ->setValue('tests/Unit/Common/Adapter/Validation/Validations/Fixtures/file.js')
            ->file('2M', false, null)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateFileError(): void
    {
        $return = $this->object
            ->setValue('tests/Unit/Common/Adapter/Validation/Validations/Fixtures/file.js')
            ->file('2M', false, 'image/png')
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidatePositiveOk(): void
    {
        $return = $this->object
            ->setValue(1)
            ->positive()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidatePositiveError(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->positive()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidatePositiveOrZeroOk(): void
    {
        $return = $this->object
            ->setValue(0)
            ->positiveOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidatePositiveOrZeroError(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->positiveOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::POSITIVE_OR_ZERO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNegativeOk(): void
    {
        $return = $this->object
            ->setValue(-1)
            ->negative()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNegativeError(): void
    {
        $return = $this->object
            ->setValue(0)
            ->negative()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NEGATIVE], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNegativeOrZeroOk(): void
    {
        $return = $this->object
            ->setValue(0)
            ->negativeOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateNegativeOrZeroError(): void
    {
        $return = $this->object
            ->setValue(1)
            ->negativeOrZero()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::NEGATIVE_OR_ZERO], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringLengthOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringLength(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringLengthError(): void
    {
        $return = $this->object
            ->setValue('123456')
            ->stringLength(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_NOT_EQUAL_LENGTH], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringMinOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringMin(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringMinError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->stringMin(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringMaxOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringMax(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringMaxError(): void
    {
        $return = $this->object
            ->setValue('123456')
            ->stringMax(5)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringRangeOk(): void
    {
        $return = $this->object
            ->setValue('12345')
            ->stringRange(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateStringRangeError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->stringRange(5, 10)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateUuIdOk(): void
    {
        $return = $this->object
            ->setValue('ea693dd6-670b-4b5e-b9fa-d324b7470afa')
            ->uuId()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateUuIdError(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->uuId()
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::UUID_TOO_SHORT], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateChoiceOk(): void
    {
        $return = $this->object
            ->setValue('b')
            ->choice(['a', 'b', 'c'], false, true, 1, 1)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([], $return,
            'validate: It was expected to return an empty array');
    }

    public function testValidateChoiceError(): void
    {
        $return = $this->object
            ->setValue('d')
            ->choice(['a', 'b', 'c'], false, true, 1, 1)
            ->validate();

        $this->assertIsArray($return,
            'validate: It was expected to be an array');

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return,
            'validate: It was expected to return an empty array');
    }
}
