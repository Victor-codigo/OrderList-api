<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\EMAIL_TYPES;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationGeneralTest extends TestCase
{
    private ValidationInterface $object;

    #[Override]
    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validateNotBlankOk(): void
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

    /** @test */
    public function validateNotBlankError(): void
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

    /** @test */
    public function validateNotNullOk(): void
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

    /** @test */
    public function validateNotNullError(): void
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

    /** @test */
    public function validateEmailOk(): void
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

    /** @test */
    public function validateEmailError(): void
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

    /** @test */
    public function validateTypeOk(): void
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

    /** @test */
    public function validateTypeError(): void
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

    /** @test */
    public function validateUniqueOk(): void
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

    /** @test */
    public function validateUniqueError(): void
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
}
