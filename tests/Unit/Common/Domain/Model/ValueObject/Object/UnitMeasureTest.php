<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use PHPUnit\Framework\TestCase;

class UnitMeasureTest extends TestCase
{
    private ValidationChain $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheUnitMeasureType(): void
    {
        $object = new UnitMeasure(UNIT_MEASURE_TYPE::UNITS);
        $return = $this->validation->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateIsNull(): void
    {
        $object = new UnitMeasure(null);
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL], $return);
    }

    /** @test */
    public function itShouldFailIsNotUnitMeasureType(): void
    {
        $object = new UnitMeasure(new \stdClass());
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
