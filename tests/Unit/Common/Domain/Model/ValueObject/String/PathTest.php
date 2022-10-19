<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;

class PathTest extends TestCase
{
    private ValidationInterface $validation;

    public function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    private function createPath(string $path): Path
    {
        return new Path($path);
    }

    public function testPathOk(): void
    {
        $path = $this->createPath('/this/is/a/valid/path');
        $return = $this->validation->validateValueObject($path);

        $this->assertEmpty($return,
            'It was expected that doesn\'t return errors');
    }

    public function testPathNotBlankAndShort(): void
    {
        $path = $this->createPath('');
        $return = $this->validation->validateValueObject($path);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT], $return,
            'It was expected that doesn\'t return errors');
    }

    public function testPathNotTooLong(): void
    {
        $path = $this->createPath(str_repeat('-', VALUE_OBJECTS_CONSTRAINTS::IMAGE_MAX_LENGTH + 1));
        $return = $this->validation->validateValueObject($path);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return);
    }
}
