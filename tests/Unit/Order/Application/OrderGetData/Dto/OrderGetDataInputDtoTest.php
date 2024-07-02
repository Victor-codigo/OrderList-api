<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderGetData\Dto\OrderGetDataInputDto;
use PHPUnit\Framework\TestCase;

class OrderGetDataInputDtoTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string LIST_ORDERS_ID = 'cd82abda-3bd6-44b8-8ff6-4ecd80ea6840';
    private const array ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
    ];

    private ValidationInterface $validator;
    private UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateListOrdersIdOrdersIdFilterSectionTextAndValueAreNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            null,
            1,
            10,
            false,
            null,
            null,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            null,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            'wrong id',
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            'wrong id',
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailOrdersIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            array_merge(self::ORDERS_ID, ['wrong id']),
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            null,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            -1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            null,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            -1,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailFilterSectionIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            'wrong filter',
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['section_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterTextIsWrong(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            'wrong filter',
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['text_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterValueIsNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            FILTER_SECTION::ORDER->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'section_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'text_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailFilterSectionOrFilterTextIsNull(): void
    {
        $object = new OrderGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LIST_ORDERS_ID,
            self::ORDERS_ID,
            1,
            10,
            true,
            null,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'filter value'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
