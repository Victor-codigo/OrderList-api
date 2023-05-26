<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductSetShopPrice;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductSetShopPriceControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/products/price';
    private const METHOD = 'PATCH';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_OTHER_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';

    /** @test */
    public function itShouldSetThePriceOfAProductForAShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['product_id', 'shop_id', 'price'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product of a shop set', $responseContent->message);
        $this->assertEquals(self::PRODUCT_ID, $responseContent->data->product_id);
        $this->assertEquals(self::SHOP_ID, $responseContent->data->shop_id);
        $this->assertEquals(0, $responseContent->data->price);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => null,
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->product_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => 'wrong id',
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'price' => 0,
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
    public function itShouldFailSettingThePriceOfAProductForAShopShopIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => null,
                'group_id' => self::GROUP_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shop_id);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopShopIdWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => 'wrong id',
                'group_id' => self::GROUP_ID,
                'price' => 0,
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
    public function itShouldFailSettingThePriceOfAProductForAShopGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => null,
                'price' => 0,
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
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => 'wrong id',
                'price' => 0,
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
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'price' => -1,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['price'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['positive_or_zero'], $responseContent->errors->price);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => '140eae6d-5f40-44c4-8a50-d3f8f7825c5c',
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_OTHER_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);
        $this->assertEquals('Product not found', $responseContent->errors->product_not_found);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopShopNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => '140eae6d-5f40-44c4-8a50-d3f8f7825c5c',
                'group_id' => self::GROUP_OTHER_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);
        $this->assertEquals('Product not found', $responseContent->errors->product_not_found);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopProductNotInTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_OTHER_ID,
                'price' => 0,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);
        $this->assertEquals('Product not found', $responseContent->errors->product_not_found);
    }

    /** @test */
    public function itShouldFailSettingThePriceOfAProductForAShopUserNotBelongToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'product_id' => self::PRODUCT_ID,
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'price' => 0,
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
