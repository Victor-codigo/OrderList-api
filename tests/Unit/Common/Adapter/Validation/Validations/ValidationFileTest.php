<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class ValidationFileTest extends TestCase
{
    private ValidationInterface $object;

    public function setUp(): void
    {
        $this->object = new ValidationChain();
    }

    /** @test */
    public function validateFileOk(): void
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

    /** @test */
    public function validateFileError(): void
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
}
