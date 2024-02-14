<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\GetProductShopPrice;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GetProductShopPriceControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/products/price';
    private const METHOD = 'GET';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_OTHER_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const SHOP_ID_2 = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';
    private const SHOP_ID_3 = 'b9b1c541-d41e-4751-9ecb-4a1d823c0405';
    private const PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const PRODUCT_ID_2 = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const PRODUCT_ID_3 = '8b6d650b-7bb7-4850-bf25-36cda9bce801';

    private function assertProductShopIsOk(object $productShopExpected, object $productShopActual): void
    {
        $this->assertContains($productShopActual->product_id, explode(',', $productShopExpected->productsId));
        $this->assertContains($productShopActual->shop_id, explode(',', $productShopExpected->shopsId));
        $this->assertContains($productShopActual->price, $productShopExpected->prices);
        $this->assertContains($productShopActual->unit, $productShopExpected->units);
    }

    /** @test */
    public function itShouldGetThePriceOfAProductForAShop(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::CM->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        $this->assertProductShopIsOk((object) $requestData, $responseContent->data[0]);
    }

    /** @test */
    public function itShouldGetThePriceOfAProductsForAShop(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
                self::PRODUCT_ID_2,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
                10.50,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::CM->value,
                UNIT_MEASURE_TYPE::UNITS->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        foreach ($responseContent->data as $productShopData) {
            $this->assertProductShopIsOk((object) $requestData, $productShopData);
        }
    }

    /** @test */
    public function itShouldGetThePriceOfAProductForADifferentShops(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
                self::SHOP_ID_2,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::CM->value,
                UNIT_MEASURE_TYPE::UNITS->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        foreach ($responseContent->data as $productShopData) {
            $this->assertProductShopIsOk((object) $requestData, $productShopData);
        }
    }

    /** @test */
    public function itShouldGetThePriceOfAProductsForADifferentShops(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
                self::PRODUCT_ID_2,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
                self::SHOP_ID_2,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
                10.50,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::CM->value,
                UNIT_MEASURE_TYPE::UNITS->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        foreach ($responseContent->data as $productShopData) {
            $this->assertProductShopIsOk((object) $requestData, $productShopData);
        }
    }

    /** @test */
    public function itShouldGetThePriceOfAProductForAllShops(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
                self::SHOP_ID_2,
                self::SHOP_ID_3,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::CM->value,
                UNIT_MEASURE_TYPE::UNITS->value,
                UNIT_MEASURE_TYPE::KG->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        foreach ($responseContent->data as $productShopData) {
            $this->assertProductShopIsOk((object) $requestData, $productShopData);
        }
    }

    /** @test */
    public function itShouldGetThePriceOfAllProductsOfAShop(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
                self::PRODUCT_ID_2,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
            'prices' => [
                null,
                10.50,
            ],
            'units' => [
                UNIT_MEASURE_TYPE::UNITS->value,
                UNIT_MEASURE_TYPE::CM->value,
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products prices', $responseContent->message);

        foreach ($responseContent->data as $productShopData) {
            $this->assertProductShopIsOk((object) $requestData, $productShopData);
        }
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductGroupIsNull(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductGroupIsWrong(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => 'wrong id',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductProductIsWrong(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                'wrong id',
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->products_id);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductShopIsWrong(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                'wrong id',
            ]),
            'groupId' => self::GROUP_ID,
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->shops_id);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductShopIsNullAndProductIdIsNull(): void
    {
        $requestData = [
            'groupId' => self::GROUP_ID,
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id_empty', 'shops_id_empty'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank'], $responseContent->errors->products_id_empty);
        $this->assertEquals(['not_blank'], $responseContent->errors->shops_id_empty);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductUserNotBelongsToTheGroup(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                self::PRODUCT_ID,
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
        ];

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
        $this->assertEquals('You not belong to the group', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailGettingThePriceOfAProductProductNotFound(): void
    {
        $requestData = [
            'productsId' => implode(',', [
                'adb4cbfa-d5ba-462d-9bfb-04087e75d6c2',
            ]),
            'shopsId' => implode(',', [
                self::SHOP_ID,
            ]),
            'groupId' => self::GROUP_ID,
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$requestData['productsId']}"
                ."&shops_id={$requestData['shopsId']}"
                ."&group_id={$requestData['groupId']}"
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);
        $this->assertEquals('Product not found', $responseContent->errors->products_not_found);
    }
}
