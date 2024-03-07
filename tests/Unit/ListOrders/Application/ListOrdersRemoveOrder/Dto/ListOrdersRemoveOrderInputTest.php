<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersRemoveOrder\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveOrderInputTest extends TestCase
{
    private const GROUP_ID = '9654da5d-b184-454a-b10b-bf6570b44afb';
    private const LISTS_ORDERS_ID = [
        '5dc59e28-6ad7-4989-8fa5-1232e49c7a13',
        '479182ef-860b-4626-a317-4649ac7ebb7b',
        '68f5c8ec-d819-4296-a658-c8199a5d025e',
    ];

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrdersRemoveOrderInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::LISTS_ORDERS_ID,
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailValidatingListsOrdersIsNull(): void
    {
        $object = new ListOrdersRemoveOrderInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['lists_orders_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailValidatingListsOrdersIdIsWrong(): void
    {
        $object = new ListOrdersRemoveOrderInputDto(
            $this->userSession,
            self::GROUP_ID,
            ['wrong id']
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['lists_orders_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIsNull(): void
    {
        $object = new ListOrdersRemoveOrderInputDto(
            $this->userSession,
            null,
            self::LISTS_ORDERS_ID,
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIsWrong(): void
    {
        $object = new ListOrdersRemoveOrderInputDto(
            $this->userSession,
            'wrong id',
            self::LISTS_ORDERS_ID
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
