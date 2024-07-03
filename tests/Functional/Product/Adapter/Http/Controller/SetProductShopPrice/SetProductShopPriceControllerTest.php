<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\SetProductShopPrice;

use stdClass;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class SetProductShopPriceControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/products/price';
    private const string METHOD = 'PUT';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_OTHER_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const array SHOPS_ID = [
        'e6c1d350-f010-403c-a2d4-3865c14630ec',
        'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
    ];
    private const array PRODUCTS_ID = [
        '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
        '8b6d650b-7bb7-4850-bf25-36cda9bce801',
    ];
    private const array PRICES = [
        10,
        20,
    ];
    private const array UNITS = [
        UNIT_MEASURE_TYPE::UNITS,
        UNIT_MEASURE_TYPE::KG,
    ];

    /** @test */
    public function itShouldSetThePriceOfAProductForShops(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);

        foreach (self::PRODUCTS_ID as $index => $productId) {
            $this->assertContainsEquals([
                    'group_id' => self::GROUP_ID,
                    'product_id' => self::PRODUCTS_ID[0],
                    'shop_id' => self::SHOPS_ID[$index],
                    'price' => self::PRICES[$index],
                    'unit' => self::UNITS[$index]->value,
                ],
                array_map(fn (stdClass $productShop) => (array) $productShop, $responseContent->data)
            );
        }
    }

    /** @test */
    public function itShouldSetForAShopProductsPrices(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => null,
                'shop_id' => self::SHOPS_ID[0],
                'products_or_shops_id' => self::PRODUCTS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);

        foreach (self::PRODUCTS_ID as $index => $productId) {
            $this->assertContainsEquals([
                    'group_id' => self::GROUP_ID,
                    'product_id' => self::PRODUCTS_ID[$index],
                    'shop_id' => self::SHOPS_ID[0],
                    'price' => self::PRICES[$index],
                    'unit' => self::UNITS[$index]->value,
                ],
                array_map(fn (stdClass $productShop) => (array) $productShop, $responseContent->data)
            );
        }
    }

    /** @test */
    public function itShouldSetThePriceOfAProductForAShopProductNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_OTHER_ID,
                'product_id' => '140eae6d-5f40-44c4-8a50-d3f8f7825c5c',
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);
    }

    /** @test */
    public function itShouldSetThePriceOfAProductForAShopShopIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_OTHER_ID,
                'product_id' => null,
                'shop_id' => '140eae6d-5f40-44c4-8a50-d3f8f7825c5c',
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);
    }

    /** @test */
    public function itShouldSetThePriceOfAProductForAShopProductsOrShopsIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_OTHER_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => ['140eae6d-5f40-44c4-8a50-d3f8f7825c5c', self::SHOPS_ID[1]],
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);
    }

    /** @test */
    public function itShouldSetThePriceOfAProductForAShopProductNotInTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_OTHER_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product, shop and price set', $responseContent->message);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopShopsPricesAndUnitsNotEquals(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => [self::SHOPS_ID[0]],
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_or_shops_prices_units_not_equals'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_equal_to'], $responseContent->errors->products_or_shops_prices_units_not_equals);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopShopsPricesAndUnitsDifferentNotEquals(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => [UNIT_MEASURE_TYPE::UNITS],
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_or_shops_prices_units_not_equals'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_equal_to'], $responseContent->errors->products_or_shops_prices_units_not_equals);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductsOrShopsIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => null,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_or_shops_prices_units_not_equals'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_equal_to'], $responseContent->errors->products_or_shops_prices_units_not_equals);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductsOrShopsIdValueIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => [null, null],
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_or_shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['not_blank', 'not_null'], ['not_blank', 'not_null']], $responseContent->errors->products_or_shops_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => null,
                'shops_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id_and_shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_null'], $responseContent->errors->product_id_and_shop_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => 'wrong id',
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->product_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopShopIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => null,
                'shop_id' => 'wrong id',
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->shop_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductsOrShopsIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => [self::SHOPS_ID[0], 'wrong id'],
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_or_shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->products_or_shops_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopGroupIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'wrong id',
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopPriceIsNegative(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => [self::PRICES[0], -1],
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['prices'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['positive_or_zero']], $responseContent->errors->prices);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopUnitIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => [null, null],
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['units'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['choice_not_such'], ['choice_not_such']], $responseContent->errors->units);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopUserNotBelongToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCTS_ID[0],
                'shop_id' => null,
                'products_or_shops_id' => self::SHOPS_ID,
                'prices' => self::PRICES,
                'units' => array_map(fn (UNIT_MEASURE_TYPE $unit) => $unit->value, self::UNITS),
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have no permissions', $responseContent->message);
        $this->assertEquals('You have no permissions', $responseContent->errors->permissions);
    }
}
