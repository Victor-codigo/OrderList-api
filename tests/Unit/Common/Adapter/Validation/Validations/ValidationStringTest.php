<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\PROTOCOLS;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationStringTest extends TestCase
{
    private ValidationInterface $object;

    #[Override]
    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validateStringLengthOk(): void
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

    /** @test */
    public function validateStringLengthError(): void
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

    /** @test */
    public function validateStringMinOk(): void
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

    /** @test */
    public function validateStringMinError(): void
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

    /** @test */
    public function validateStringMaxOk(): void
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

    /** @test */
    public function validateStringMaxError(): void
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

    /** @test */
    public function validateStringRangeOk(): void
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

    /** @test */
    public function validateStringRangeError(): void
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

    /** @test */
    public function validateUuIdOk(): void
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

    /** @test */
    public function validateUuIdError(): void
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

    /** @test */
    public function validateRegExOk(): void
    {
        $return = $this->object
            ->setValue('123')
            ->regEx('/^[0-9]{3}$/i')
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateRegExFail(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->regEx('/^[0-9]{3}$/i')
            ->validate();

        $this->assertIsArray($return);
        $this->assertEquals([VALIDATION_ERRORS::REGEX_FAIL], $return);
    }

    /** @test */
    public function validateRegExOkWhenFailPattern(): void
    {
        $return = $this->object
            ->setValue('1234')
            ->regEx('/^[0-9]{3}$/i', false)
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateAlphanumericOk(): void
    {
        $return = $this->object
            ->setValue('1234_ab')
            ->alphanumeric()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateAlphanumericFail(): void
    {
        $return = $this->object
            ->setValue('1234_ab-')
            ->alphanumeric()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEquals([VALIDATION_ERRORS::ALPHANUMERIC], $return);
    }

    /** @test */
    public function validateAlphanumericAndRegex(): void
    {
        $return = $this->object
            ->setValue('1234_ab-')
            ->regEx('/^[0-9]$/')
            ->alphanumeric()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEquals([VALIDATION_ERRORS::REGEX_FAIL, VALIDATION_ERRORS::ALPHANUMERIC], $return);
    }

    /** @test */
    public function validateUrlProtocolHttpOk(): void
    {
        $return = $this->object
            ->setValue('http://subdomain.domain.com')
            ->url()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateUrlProtocolHttpsOk(): void
    {
        $return = $this->object
            ->setValue('https://subdomain.domain.com')
            ->url()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateUrlNoProtocolOk(): void
    {
        $return = $this->object
            ->setValue('//subdomain.domain.com')
            ->url()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateUrlProtocolFtoNotValidByDefault(): void
    {
        $return = $this->object
            ->setValue('ftp://subdomain.domain.com')
            ->url()
            ->validate();

        $this->assertIsArray($return);
        $this->assertSame([VALIDATION_ERRORS::URL], $return);
    }

    /** @test */
    public function validateUrlProtocolWrong(): void
    {
        $return = $this->object
            ->setValue('htt://subdomain.domain.com')
            ->url([PROTOCOLS::HTTP, PROTOCOLS::HTTPS])
            ->validate();

        $this->assertIsArray($return);
        $this->assertSame([VALIDATION_ERRORS::URL], $return);
    }

    /** @test */
    public function validateUrlNoProtocol(): void
    {
        $return = $this->object
            ->setValue('subdomain.domain.com')
            ->url([PROTOCOLS::HTTP, PROTOCOLS::HTTPS])
            ->validate();

        $this->assertIsArray($return);
        $this->assertSame([VALIDATION_ERRORS::URL], $return);
    }

    /** @test */
    public function validateLanguageOk(): void
    {
        $return = $this->object
            ->setValue('en')
            ->language()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateLanguageFail(): void
    {
        $return = $this->object
            ->setValue('esp')
            ->language()
            ->validate();

        $this->assertIsArray($return);
        $this->assertSame([VALIDATION_ERRORS::LANGUAGE], $return);
    }

    /** @test */
    public function validateJsonOk(): void
    {
        $return = $this->object
            ->setValue(json_encode(['param1' => 'value1']))
            ->json()
            ->validate();

        $this->assertIsArray($return);
        $this->assertEmpty($return);
    }

    /** @test */
    public function validateJsonFail(): void
    {
        $return = $this->object
            ->setValue('not valid json')
            ->json()
            ->validate();

        $this->assertIsArray($return);
        $this->assertSame([VALIDATION_ERRORS::JSON], $return);
    }
}
