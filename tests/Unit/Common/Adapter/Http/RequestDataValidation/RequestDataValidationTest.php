<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\RequestDataValidation;

use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use PHPUnit\Framework\TestCase;

class RequestDataValidationTest extends TestCase
{
    private object $object;
    private \ReflectionMethod $objectMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new class() {
            use RequestDataValidation;
        };

        $objectReflection = new \ReflectionClass($this->object);
        $this->objectMethod = $objectReflection->getMethod('validateArrayOverflow');
        $this->objectMethod->setAccessible(true);
    }

    /** @test */
    public function itShouldReturnNullValuesAreNull(): void
    {
        $values = null;
        $valuesMax = 100;
        $return = $this->objectMethod->invoke($this->object, $values, $valuesMax);

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldReturnOnly100Values(): void
    {
        $values = array_fill(0, 150, 'value');
        $valuesMax = 100;
        $return = $this->objectMethod->invoke($this->object, $values, $valuesMax);

        $this->assertCount($valuesMax, $return);
    }

    /** @test */
    public function itShouldReturnAllValues(): void
    {
        $values = array_fill(0, 50, 'value');
        $valuesMax = 100;
        $return = $this->objectMethod->invoke($this->object, $values, $valuesMax);

        $this->assertCount(50, $return);
    }
}
