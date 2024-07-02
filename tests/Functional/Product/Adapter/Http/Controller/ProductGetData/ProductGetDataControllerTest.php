<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/products';
    private const string METHOD = 'GET';
    private const string USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const string USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const string GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_ID_NOT_PERMISSIONS = '0dc4ec43-13c4-31cf-a3a3-aca81e96a4c8';
    private const array PRODUCTS_ID = [
        '8b6d650b-7bb7-4850-bf25-36cda9bce801',
        '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
        'afc62bc9-c42c-4c4d-8098-09ce51414a92',
    ];
    private const array SHOPS_ID = [
        'e6c1d350-f010-403c-a2d4-3865c14630ec',
        'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function assertResponseDataIsOk(int $pageExpected, int $pagesTotalExpected, array $productDataExpected, object $responseActual): void
    {
        $this->assertTrue(property_exists($responseActual, 'page'));
        $this->assertTrue(property_exists($responseActual, 'pages_total'));
        $this->assertTrue(property_exists($responseActual, 'products'));

        $this->assertCount(count($productDataExpected), $responseActual->products);
        $this->assertEquals($pageExpected, $responseActual->page);
        $this->assertEquals($pagesTotalExpected, $responseActual->pages_total);

        foreach ($responseActual->products as $key => $productActual) {
            $this->assertProductDataIsOk($productDataExpected[$key], $productActual);
        }
    }

    private function assertProductDataIsOk(array $productDataExpected, object $productDataActual): void
    {
        $this->assertTrue(property_exists($productDataActual, 'id'));
        $this->assertTrue(property_exists($productDataActual, 'group_id'));
        $this->assertTrue(property_exists($productDataActual, 'name'));
        $this->assertTrue(property_exists($productDataActual, 'description'));
        $this->assertTrue(property_exists($productDataActual, 'image'));
        $this->assertTrue(property_exists($productDataActual, 'created_on'));

        $this->assertEquals($productDataExpected['id'], $productDataActual->id);
        $this->assertEquals($productDataExpected['group_id'], $productDataActual->group_id);
        $this->assertEquals($productDataExpected['name'], $productDataActual->name);
        $this->assertEquals($productDataExpected['image'], $productDataActual->image);
        $this->assertIsString($productDataActual->created_on);
    }

    private function getProductsData(): array
    {
        $appProtocolAndDomain = static::getContainer()->getParameter('common.app.protocolAndDomain');
        $productPublicImagePath = static::getContainer()->getParameter('product.public.image.path');

        return [
            [
                'id' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Juan Carlos',
                'description' => null,
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Juanola',
                'description' => 'Product description 1',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => 'afc62bc9-c42c-4c4d-8098-09ce51414a92',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Product description 1',
                'image' => "{$appProtocolAndDomain}{$productPublicImagePath}/fileName.file",
                'created_on' => '',
            ],
            [
                'id' => 'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Perico',
                'description' => 'Product description 1',
                'image' => null,
                'created_on' => '',
            ],
        ];
    }

    /** @test */
    public function itShouldGetProductsOfAGroupOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                .'&order_asc=true',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk($page, 1, $productDataExpected, $responseContent->data);
    }

    /** @test */
    public function itShouldGetProductsOfAGroupOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                .'&order_asc=false',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk($page, 1, array_reverse($productDataExpected), $responseContent->data);
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByProductIdOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', self::PRODUCTS_ID);
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                .'&order_asc=true',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[0],
                $productDataExpected[1],
                $productDataExpected[2],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByProductIdOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', self::PRODUCTS_ID);
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        usort(
            $productDataExpected,
            fn (array $productA, array $productB) => strcmp((string) $productA['name'], (string) $productB['name'])
        );
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[0],
                $productDataExpected[1],
                $productDataExpected[2],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByShopIdOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', self::SHOPS_ID);
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1, [
                $productDataExpected[0],
                $productDataExpected[1],
                $productDataExpected[2],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByShopIdOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', self::SHOPS_ID);
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        usort(
            $productDataExpected,
            fn (array $productA, array $productB) => strcmp((string) $productA['name'], (string) $productB['name'])
        );
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[0],
                $productDataExpected[1],
                $productDataExpected[2],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByProductName(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productName = 'Juanola';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name={$productName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk($page, 1, [$productDataExpected[1]], $responseContent->data);
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByProductNameFilterOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $productNameFilterValue = 'Ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[0],
                $productDataExpected[1],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByProductNameFilterOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $productNameFilterValue = 'Ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                .'&order_asc=false',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[1],
                $productDataExpected[0],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByShopNameFilterOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Shop name 1';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[1],
                $productDataExpected[2],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupByShopNameFilterOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Shop name 1';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}"
                .'&order_asc=false',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            1,
            [
                $productDataExpected[2],
                $productDataExpected[1],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldGetProductsOfAGroupPagination(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $productNameFilterValue = 'Ju';
        $page = 1;
        $pageItems = 1;

        $client = $this->getNewClientAuthenticatedUser();
        $productDataExpected = $this->getProductsData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'products'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        $this->assertResponseDataIsOk(
            $page,
            2,
            [
                $productDataExpected[0],
            ],
            $responseContent->data
        );
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupGroupIdIsNull(): void
    {
        $productId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupGroupIdIsWrong(): void
    {
        $groupId = 'wrong group id';
        $productId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupGroupIdNotFound(): void
    {
        $groupId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $productId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupGroupIdNotPermissions(): void
    {
        $groupId = self::GROUP_ID_NOT_PERMISSIONS;
        $productId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productId = 'product id is wrong';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->products_id);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductIdNotFound(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopsIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = 'shop id is wrong';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], $responseContent->errors->shops_id);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopsIdNotFound(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = 'f7d0840a-11c8-49ae-bf12-b29eb66afab4';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$shopsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productName = 'product name is wrong-';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name={$productName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->product_name);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameNotFound(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productName = 'product that not exists';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name={$productName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameFilterTypeIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterValue = 'ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->product_name_filter_type);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameFilterTypeIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = 'wrong type';
        $productNameFilterValue = 'ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->product_name_filter_type);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameFilterValueIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->product_name_filter_value);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupProductNameFilterValueIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $productNameFilterValue = 'ju-';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&product_name_filter_type={$productNameFilterType}"
                ."&product_name_filter_value={$productNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->product_name_filter_value);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopNameFilterTypeIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterValue = 'ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shop_name_filter_type);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopNameFilterTypeIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = 'wrong type';
        $shopNameFilterValue = 'ju';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shop_name_filter_type);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopNameFilterValueIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->shop_name_filter_value);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupShopNameFilterValueIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'ju-';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->shop_name_filter_value);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupPageIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupPageIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $page = -1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupPageItemsIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $page = 1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailGettingProductsOfAGroupPageItemsIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $page = 1;
        $pageItems = -1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], $responseContent->errors->page_items);
    }
}
