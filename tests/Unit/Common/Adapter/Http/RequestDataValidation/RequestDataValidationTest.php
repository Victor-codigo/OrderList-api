<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\RequestDataValidation;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use PHPUnit\Framework\TestCase;

class RequestDataValidationTest extends TestCase
{
    private object $object;
    private \ReflectionMethod $objectMethodValidateArrayOverflow;
    private \ReflectionMethod $objectMethodValidateCsvOverflow;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new class() {
            use RequestDataValidation;
        };

        $objectReflection = new \ReflectionClass($this->object);
        $this->objectMethodValidateArrayOverflow = $objectReflection->getMethod('validateArrayOverflow');
        $this->objectMethodValidateArrayOverflow->setAccessible(true);

        $this->objectMethodValidateCsvOverflow = $objectReflection->getMethod('validateCsvOverflow');
        $this->objectMethodValidateArrayOverflow->setAccessible(true);
    }

    #[Test]
    public function itShouldReturnNullValuesAreNull(): void
    {
        $values = null;
        $valuesMax = 100;
        $return = $this->objectMethodValidateArrayOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldReturnOnly100Values(): void
    {
        $values = array_fill(0, 150, 'value');
        $valuesMax = 100;
        $return = $this->objectMethodValidateArrayOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertCount($valuesMax, $return);
    }

    #[Test]
    public function itShouldReturnAllValues(): void
    {
        $values = array_fill(0, 50, 'value');
        $valuesMax = 100;
        $return = $this->objectMethodValidateArrayOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertCount(50, $return);
    }

    #[Test]
    public function itShouldReturnNullValuesCsvAreNull(): void
    {
        $values = null;
        $valuesMax = 100;
        $return = $this->objectMethodValidateCsvOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertNull($return);
    }

    #[Test]
    public function itShouldReturnOnly100ValuesCsv(): void
    {
        $values = implode(
            ',',
            array_fill(0, 150, 'value')
        );
        $valuesMax = 100;
        $return = $this->objectMethodValidateCsvOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertCount($valuesMax, $return);
    }

    #[Test]
    public function itShouldReturnAllValuesCsv(): void
    {
        $values = implode(
            ',',
            array_fill(0, 50, 'value')
        );
        $valuesMax = 100;
        $return = $this->objectMethodValidateCsvOverflow->invoke($this->object, $values, $valuesMax);

        $this->assertCount(50, $return);
    }
}
