<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object\Filter;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Filter\FilterDbLikeComparison;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use PHPUnit\Framework\TestCase;

class FilterDbLikeComparisonTest extends TestCase
{
    private FilterDbLikeComparison $object;
    private ValidationChain $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidateStarsWith(): void
    {
        $this->object = new FilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateEndsWith(): void
    {
        $this->object = new FilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateContainsWith(): void
    {
        $this->object = new FilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateEqualsWith(): void
    {
        $this->object = new FilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailFilterCanNotBeNull(): void
    {
        $this->object = new FilterDbLikeComparison(null);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function itShouldFailFilterNotValid(): void
    {
        $this->object = new FilterDbLikeComparison(new \stdClass());

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
