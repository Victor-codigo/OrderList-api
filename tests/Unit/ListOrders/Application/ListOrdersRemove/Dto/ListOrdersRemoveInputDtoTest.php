<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersRemove\Dto\ListOrdersRemoveInputDto;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveInputDtoTest extends TestCase
{
    private UserShared $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $listOrdersId = '7883e7a9-368f-4ede-988c-afd6203fc191';
        $groupId = '598ef7d7-5f11-459e-aa74-342922ef96db';
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $listOrdersId = null;
        $groupId = '598ef7d7-5f11-459e-aa74-342922ef96db';
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $listOrdersId = 'wrong id';
        $groupId = '598ef7d7-5f11-459e-aa74-342922ef96db';
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIsNull(): void
    {
        $listOrdersId = '7883e7a9-368f-4ede-988c-afd6203fc191';
        $groupId = null;
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $listOrdersId = '7883e7a9-368f-4ede-988c-afd6203fc191';
        $groupId = 'wrong id';
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailListOrdersAndGroupIdIsWrong(): void
    {
        $listOrdersId = 'wrong id';
        $groupId = 'wrong id';
        $object = new ListOrdersRemoveInputDto($this->userSession, $listOrdersId, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
                'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            ],
            $return
        );
    }
}
