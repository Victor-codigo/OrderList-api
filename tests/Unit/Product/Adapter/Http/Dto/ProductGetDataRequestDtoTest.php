<?php

declare(strict_types=1);

namespace Test\Unit\Product\Adapter\Http\Dto;

use PHPUnit\Framework\TestCase;
use Product\Adapter\Http\Controller\ProductGetData\Dto\ProductGetDataRequestDto;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class ProductGetDataRequestDtoTest extends TestCase
{
    private const ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const NUM_MAX = 100;

    private function createRequest(array|null $requestData): ProductGetDataRequestDto
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

        $request->query->set('product_name_starts_with', $requestData['product_name_starts_with']);

        return new ProductGetDataRequestDto($request);
    }

    /** @test */
    public function itShouldProcessAllData(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, self::NUM_MAX, self::ID),
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
            'product_name_starts_with' => 'jp',
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
        $this->assertEquals($request['product_name_starts_with'], $requestDto->productNameStartsWith);
    }

    /** @test */
    public function itShouldProcessAllProductsProductsIdIsNull(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => null,
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
            'product_name_starts_with' => 'jp',
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals(null, $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
        $this->assertEquals($request['product_name_starts_with'], $requestDto->productNameStartsWith);
    }

    /** @test */
    public function itShouldProcessAllProductsShopsIdIsNull(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, self::NUM_MAX, self::ID),
            'shops_id' => null,
            'product_name_starts_with' => 'jp',
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals(null, $requestDto->shopsId);
        $this->assertEquals($request['product_name_starts_with'], $requestDto->productNameStartsWith);
    }

    /** @test */
    public function itShouldNotProcessAllProducts(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, 101, self::ID),
            'shops_id' => array_fill(0, self::NUM_MAX, self::ID),
            'product_name_starts_with' => 'jp',
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals(array_slice($request['products_id'], 0, self::NUM_MAX), $requestDto->productsId);
        $this->assertEquals($request['shops_id'], $requestDto->shopsId);
        $this->assertEquals($request['product_name_starts_with'], $requestDto->productNameStartsWith);
    }

    /** @test */
    public function itShouldNotProcessAllShops(): void
    {
        $request = [
            'group_id' => self::ID,
            'products_id' => array_fill(0, 10, self::ID),
            'shops_id' => array_fill(0, 101, self::ID),
            'product_name_starts_with' => 'jp',
        ];
        $requestDto = $this->createRequest($request);

        $this->assertEquals($request['group_id'], $requestDto->groupId);
        $this->assertEquals($request['products_id'], $requestDto->productsId);
        $this->assertEquals(array_slice($request['shops_id'], 0, self::NUM_MAX), $requestDto->shopsId);
        $this->assertEquals($request['product_name_starts_with'], $requestDto->productNameStartsWith);
    }
}
