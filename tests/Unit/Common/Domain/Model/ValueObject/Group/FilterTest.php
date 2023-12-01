<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Group;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Domain\Model\ValueObject\Group\Fixtures\ENUM_TEST;

class FilterTest extends TestCase
{
    private ValidationChain $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheFilter(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createName('Peter')
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNameIsEmpty(): void
    {
        $object = new Filter(
            '',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createName('Peter')
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailTypeIsNull(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(null),
            ValueObjectFactory::createName('Peter')
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailTypeIsWrong(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(ENUM_TEST::VALUE_1),
            ValueObjectFactory::createName('Peter')
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH]], $return);
    }

    /** @test */
    public function itShouldFailValueIsNull(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createName(null)
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValueIsWrong(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createName('Peter-')
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['value' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }

    /** @test */
    public function itShouldFailValueAndTypeAreWrong(): void
    {
        $object = new Filter(
            'filter_name',
            ValueObjectFactory::createFilterDbLikeComparison(ENUM_TEST::VALUE_1),
            ValueObjectFactory::createName('Peter-')
        );

        $return = $object->validate($this->validation);

        $this->assertEquals([
            'type' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH],
            'value' => [VALIDATION_ERRORS::ALPHANUMERIC],
            ],
            $return
        );
    }
}
