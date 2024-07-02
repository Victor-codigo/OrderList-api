<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderCreate\Dto\OrderCreateInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderCreateInputDtoTest extends TestCase
{
    private const string GROUP_ID_NEW = '971c0fc0-50b4-42ad-b5b4-a4ad1f11c380';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';

    private MockObject|UserShared $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    private function getOrdersData(): array
    {
        return [
            [
                'list_orders_id' => 'c64389d0-3dc9-4a51-bc7c-ccd712308691',
                'product_id' => 'ea3534f8-0d10-48c5-b732-138c3696f0b6',
                'shop_id' => 'dd39bd72-b545-4ee1-b8a2-061ed6727dc8',
                'description' => 'order description 1',
                'amount' => 10,
            ],
            [
                'list_orders_id' => '7e847092-3ec4-40de-a2cc-9d48e77bb56f',
                'product_id' => 'ab440cbe-1834-4b6b-8743-8747a744d549',
                'shop_id' => '11ec0596-33aa-4985-b30a-81baf8c2a6cf',
                'description' => 'order description 2',
                'amount' => 20.26,
            ],
            [
                'list_orders_id' => '4750dc6b-e95f-444c-97cf-65b7651295e2',
                'product_id' => '8f73f465-027b-4495-bcf4-a1f8d2206440',
                'shop_id' => '2e6c5c67-95ee-4d2f-b5aa-80b4f25db074',
                'description' => 'order description 3',
                'amount' => 30.25,
            ],
        ];
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $ordersData = $this->getOrdersData();

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['shop_id'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateOrdersDescriptionIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['description'] = null;
        $ordersData[1]['description'] = null;
        $ordersData[2]['description'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateOrdersAmountIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['amount'] = null;
        $ordersData[1]['amount'] = null;
        $ordersData[2]['amount'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailOrdersDataIdsNull(): void
    {
        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();

        $object = new OrderCreateInputDto($this->userSession, null, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $ordersData = $this->getOrdersData();

        $object = new OrderCreateInputDto($this->userSession, 'wrong id', self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailListsOrdersIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, null, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailManyListsOrdersIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['product_id'] = null;
        $ordersData[2]['product_id'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([
            ['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
            ['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailListOrdersIsWrong(): void
    {
        $ordersData = $this->getOrdersData();

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, 'wrong id', $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailProductIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['product_id'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]]], $return);
    }

    /** @test */
    public function itShouldFailManyProductIdIsNull(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['product_id'] = null;
        $ordersData[2]['product_id'] = null;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([
            ['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
            ['product_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['product_id'] = 'wrong id';

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([['product_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['shop_id'] = 'wrong id';

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['description'] = str_pad('', 501, 'p');

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]]], $return);
    }

    /** @test */
    public function itShouldFailAmountIsLessThanZero(): void
    {
        $ordersData = $this->getOrdersData();
        $ordersData[0]['amount'] = -3;

        $object = new OrderCreateInputDto($this->userSession, self::GROUP_ID_NEW, self::LIST_ORDERS_ID, $ordersData);

        $return = $object->validate($this->validator);

        $this->assertEquals([['amount' => [VALIDATION_ERRORS::POSITIVE_OR_ZERO]]], $return);
    }
}
