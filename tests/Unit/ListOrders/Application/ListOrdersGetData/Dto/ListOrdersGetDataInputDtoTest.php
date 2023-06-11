<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataInputDto;
use PHPUnit\Framework\TestCase;

class ListOrdersGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateListOrdersNameStartsWithIsNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateListOrdersIdIsNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            null,
            'd700d726-1939-4f47-894d-a860224dc6f4',
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateListOrdersIdIsEmpty(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIdIsNull(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            null,
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIdIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                '3c6585ed-e5bd-46cd-9458-cea03b4c41a4',
                '52560574-2751-40c3-bf33-1c65c451066c',
                '81be2611-c555-44ee-ba24-a3ade9c228a7',
            ],
            'wring id',
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailValidatingListOrdersIdsAndNameStartsWithAreEmpty(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_ids_and_name_starts_with_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailValidatingListOrdersIdsAndNameStartsWithAreWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                'wrong id',
            ],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            str_pad('', 51, 'p')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'list_orders_ids' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'list_orders_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailValidatingListOrdersIdIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [
                'wrong id',
            ],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            'list name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_ids' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailValidatingListOrdersNameStartsWithIsWrong(): void
    {
        $object = new ListOrdersGetDataInputDto(
            $this->userSession,
            [],
            'd700d726-1939-4f47-894d-a860224dc6f4',
            str_pad('', 51, 'p')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }
}
