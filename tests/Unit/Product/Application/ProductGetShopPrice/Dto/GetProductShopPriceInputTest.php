<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\GetProductShopPrice\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\GetProductShopPrice\Dto\GetProductShopPriceInputDto;

class GetProductShopPriceInputTest extends TestCase
{
    private const PRODUCT_ID = '36e3e209-7ba4-4595-bde5-5e23375449f0';
    private const SHOP_ID = '6313e09c-3eb7-4ff0-995c-a4a02e2390e4';
    private const GROUP_ID = 'b7ce77da-8741-4e32-b408-12e63459a1d2';

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
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], [self::SHOP_ID], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductIsNull(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, null, [self::SHOP_ID], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductIsEmpty(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [], [self::SHOP_ID], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopIsNull(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], null, self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopIsEmpty(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], [], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailProductIdIsWrong(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, ['wrong id'], [self::SHOP_ID], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailProductIdAndShopIdAreNull(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [], [], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'products_id_empty' => [VALIDATION_ERRORS::NOT_BLANK],
                'shops_id_empty' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], ['wrong id'], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], [self::SHOP_ID], null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIsWrong(): void
    {
        $object = new GetProductShopPriceInputDto($this->userSession, [self::PRODUCT_ID], [self::SHOP_ID], 'wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
