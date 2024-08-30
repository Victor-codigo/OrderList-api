<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object\Filter;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\Filter\FilterSection;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use PHPUnit\Framework\TestCase;

class FilterSectionTest extends TestCase
{
    private FilterSection $object;
    private ValidationChain $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidateSectionListOrders(): void
    {
        $this->object = new FilterSection(FILTER_SECTION::LIST_ORDERS);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateSectionProduct(): void
    {
        $this->object = new FilterSection(FILTER_SECTION::PRODUCT);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateSectionShop(): void
    {
        $this->object = new FilterSection(FILTER_SECTION::SHOP);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateSectionOrder(): void
    {
        $this->object = new FilterSection(FILTER_SECTION::ORDER);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailValidatingValueIsNull(): void
    {
        $this->object = new FilterSection(null);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function itShouldFailValidatingValueIsWrong(): void
    {
        $this->object = new FilterSection(VALIDATION_ERRORS::ALPHANUMERIC);

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
