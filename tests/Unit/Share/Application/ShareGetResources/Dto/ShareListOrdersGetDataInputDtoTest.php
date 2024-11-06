<?php

declare(strict_types=1);

namespace Test\Unit\Share\Application\ShareGetResources\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataInputDto;

class ShareListOrdersGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            10,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateFilterTextIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            10,
            null,
            null
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateFilterTextIsWrongAndFilterValueIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            10,
            'wrong filter',
            null
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            null,
            1,
            10,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            'wrong list orders id',
            1,
            10,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailPageIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            null,
            10,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailPageIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            -1,
            10,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    #[Test]
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            null,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            -1,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    #[Test]
    public function itShouldValidateFilterTextIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            10,
            'wrong filter',
            'order'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['text_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
