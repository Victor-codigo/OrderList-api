<?php

declare(strict_types=1);

namespace Test\Unit\Product\Adapter\Http\GetProductShopPriceRequestDto;

use PHPUnit\Framework\TestCase;
use Product\Adapter\Http\Controller\GetProductShopPrice\Dto\GetProductShopPriceRequestDto;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class GetProductShopPriceRequestDtoTest extends TestCase
{
    private const string ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const int NUM_MAX = 100;

    private function createRequest(array|null $requestData): GetProductShopPriceRequestDto
    {
        $request = new Request();
        $request->query = new InputBag();
        $request->query->set('group_id', $requestData['group_id']);

        if (null !== $requestData['products_id']) {
            $request->query->set('products_id', implode(',', $requestData['products_id']));
        }

        if (null !== $requestData['shops_id']) {
            $request->query->set('shops_id', implode(',', $requestData['shops_id']));
        }

        return new GetProductShopPriceRequestDto($request);
    }

    /** @test */
    public function itShouldProcessAllData(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, self::NUM_MAX, self::ID),
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
    }

    /** @test */
    public function itShouldProcessAllProductsProductsIdIsNull(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => null,
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals(null, $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
    }

    /** @test */
    public function itShouldProcessAllProductsShopsIdIsNull(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, self::NUM_MAX, self::ID),
            'shops_id' => null,
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals(null, $requestDto->shopsId);
    }

    /** @test */
    public function itShouldNotProcessAllProducts(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, 101, self::ID),
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals(array_slice($request['products_id'], 0, self::NUM_MAX), $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
    }

    /** @test */
    public function itShouldNotProcessAllShops(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, 10, self::ID),
            'shops_id' => array_fill(0, 101, self::ID),
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals(array_slice($request['shops_id'], 0, self::NUM_MAX), $requestDto->shopsId);
    }
}
