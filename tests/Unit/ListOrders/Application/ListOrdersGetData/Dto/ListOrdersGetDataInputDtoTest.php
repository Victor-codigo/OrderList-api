<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersGetData\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataInputDto;
use PHPUnit\Framework\TestCase;

class ListOrdersGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            'list name',
            true,
            FILTER_SECTION::LIST_ORDERS->value,
            FILTER_STRING_COMPARISON::STARTS_WITH->value,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateListOrdersIdIsNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null,
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateListOrdersIdIsEmpty(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateAllParametersAreNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null,
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateFilterSection(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null,
            'filter value',
            true,
            FILTER_SECTION::LIST_ORDERS->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateFilterText(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null,
            'filter value',
            false,
            FILTER_SECTION::LIST_ORDERS->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            null,
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'wring id',
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailValidatingListOrdersIdIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [
                'wrong id',
            ],
            null,
            true,
            null,
            null,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    #[Test]
    public function itShouldFailValidatingFilterSectionValueIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'wrong value-',
            true,
            FILTER_SECTION::LIST_ORDERS->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'section_filter_value' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
            'text_filter_value' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
        ],
            $return
        );
    }

    #[Test]
    public function itShouldFailValidatingFilterSectionIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'filter value',
            true,
            'wrong filter section',
            FILTER_STRING_COMPARISON::EQUALS->value,
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['section_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingFilterTextIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'filter value',
            true,
            FILTER_SECTION::LIST_ORDERS->value,
            'wrong filter text',
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['text_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingFilterSectionAndTextAreNotBothPresent(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'filter value',
            true,
            null,
            'equals',
            1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingPageIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'filter value',
            true,
            null,
            null,
            -1,
            10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    #[Test]
    public function itShouldFailValidatingPageItemsIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            [],
            'filter value',
            true,
            null,
            null,
            1,
            -10
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
