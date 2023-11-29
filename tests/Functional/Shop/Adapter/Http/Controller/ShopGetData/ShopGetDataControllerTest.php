<?php

declare(strict_types=1);

namespace Test\Functional\Shop\Adapter\Http\Controller\ShopGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Shop\Application\ShopGetData\SHOP_GET_DATA_FILTER;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShopGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/shops';
    private const METHOD = 'GET';
    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const SHOP_EXISTS_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const SHOP_EXISTS_ID_2 = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';
    private const SHOP_EXISTS_ID_3 = 'b9b1c541-d41e-4751-9ecb-4a1d823c0405';
    private const PRODUCT_EXISTS_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';

    private function assertShopDataIsOk(array $shopsDataExpected, object $shopDataActual): void
    {
        $this->assertTrue(property_exists($shopDataActual, 'id'));
        $this->assertTrue(property_exists($shopDataActual, 'group_id'));
        $this->assertTrue(property_exists($shopDataActual, 'name'));
        $this->assertTrue(property_exists($shopDataActual, 'description'));
        $this->assertTrue(property_exists($shopDataActual, 'image'));
        $this->assertTrue(property_exists($shopDataActual, 'created_on'));

        $this->assertEquals($shopsDataExpected['id'], $shopDataActual->id);
        $this->assertEquals($shopsDataExpected['group_id'], $shopDataActual->group_id);
        $this->assertEquals($shopsDataExpected['name'], $shopDataActual->name);
        $this->assertEquals($shopsDataExpected['image'], $shopDataActual->image);
        $this->assertIsString($shopDataActual->created_on);
    }

    /** @test */
    public function itShouldGetShops(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $orderAsc = true;
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            'id' => self::SHOP_EXISTS_ID,
            'group_id' => self::GROUP_EXISTS_ID,
            'name' => 'Shop name 1',
            'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
            'image' => null,
            'created_on' => '',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&order_asc={$orderAsc}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsWithShopId(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
                'id' => self::SHOP_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 1',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
        ];

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

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsWithProductsOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $orderArc = true;
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            [
                'id' => self::SHOP_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 1',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => self::SHOP_EXISTS_ID_2,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 2',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => self::SHOP_EXISTS_ID_3,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 3',
                'description' => null,
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productsId}"
                ."&order_asc={$orderArc}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $key => $shopData) {
            $this->assertShopDataIsOk($shopDataExpected[$key], $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsShopName(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopName = 'Shop name 2';
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            'id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
            'group_id' => self::GROUP_EXISTS_ID,
            'name' => 'Shop name 2',
            'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            'image' => null,
            'created_on' => '',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name={$shopName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsShopNameFilterValueOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $orderAsc = false;
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            [
                'id' => 'cc7f5dd6-02ba-4bd9-b5c1-5b65d81e59a0',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 4',
                'description' => null,
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => 'b9b1c541-d41e-4751-9ecb-4a1d823c0405',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 3',
                'description' => null,
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 2',
                'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 1',
                'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&order_asc={$orderAsc}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2, 3], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $key => $shopData) {
            $this->assertShopDataIsOk($shopDataExpected[$key], $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsWithProductsAndShopsId(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            'id' => self::SHOP_EXISTS_ID,
            'group_id' => self::GROUP_EXISTS_ID,
            'name' => 'Shop name 1',
            'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            'image' => null,
            'created_on' => '',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsWithProductsAndShopName(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Shop name 2';
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            'id' => self::SHOP_EXISTS_ID_2,
            'group_id' => self::GROUP_EXISTS_ID,
            'name' => 'Shop name 2',
            'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            'image' => null,
            'created_on' => '',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsWithProductsAndShopNameStartsWithOrderAsc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $orderAsc = true;
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            [
                'id' => self::SHOP_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 1',
                'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => self::SHOP_EXISTS_ID_2,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 2',
                'description' => 'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => self::SHOP_EXISTS_ID_3,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Shop name 3',
                'description' => null,
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&order_asc={$orderAsc}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $key => $shopData) {
            $this->assertShopDataIsOk($shopDataExpected[$key], $shopData);
        }
    }

    /** @test */
    public function itShouldGetShopsNoFilter(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $orderAsc = true;
        $page = 1;
        $pageItems = 100;

        $shopDataExpected = [
            'id' => self::SHOP_EXISTS_ID,
            'group_id' => self::GROUP_EXISTS_ID,
            'name' => 'Shop name 1',
            'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
            'image' => null,
            'created_on' => '',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&order_asc={$orderAsc}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shops data', $responseContent->message);

        foreach ($responseContent->data as $shopData) {
            $this->assertShopDataIsOk($shopDataExpected, $shopData);
        }
    }

    /** @test */
    public function itShouldFailNoShopsFoundWinShopName(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopName = 'shop name not exists';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name={$shopName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailNoShopsFound(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', ['f7d3f835-9c72-4dda-821c-73e4f3e878a5']);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Ma';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailNoPersmissionsInTheGroup(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);
    }

    /** @test */
    public function itShouldFailGroupIsNull(): void
    {
        $groupId = null;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailShopsIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', ['wong id']);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shops_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame([['uuid_invalid_characters']], $responseContent->errors->shops_id);
    }

    /** @test */
    public function itShouldFailProductsIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', ['wrong id']);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Ma';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['products_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame([['uuid_invalid_characters']], $responseContent->errors->products_id);
    }

    /** @test */
    public function itShouldFailShopNameIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopName = 'shop name-';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name={$shopName}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['alphanumeric_with_whitespace'], $responseContent->errors->shop_name);
    }

    /** @test */
    public function itShouldFailShopNameFilterValueIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->shop_filter_value);
    }

    /** @test */
    public function itShouldFailShopNameFilterValueIsToolLong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = str_pad('', 51, 'p');
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_value'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['string_too_long'], $responseContent->errors->shop_filter_value);
    }

    /** @test */
    public function itShouldFailShopNameShopNameFilterTypeIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->shop_filter_type);
    }

    /** @test */
    public function itShouldFailShopNameShopNameFilterTypeIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = 'wrong filter type';
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->shop_filter_type);
    }

    /** @test */
    public function itShouldFailShopNameShopNameFilterNameIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
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

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['choice_not_such'], $responseContent->errors->shop_filter_name);
    }

    /** @test */
    public function itShouldFailShopNameShopNameFilterNameIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameFilterName = 'wrong filter name';
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = 'Sho';
        $page = 1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_filter_name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['choice_not_such'], $responseContent->errors->shop_filter_name);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = str_pad('', 51, 'p');
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = str_pad('', 51, 'p');
        $page = -1;
        $pageItems = 100;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['greater_than'], $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = str_pad('', 51, 'p');
        $page = 1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['greater_than'], $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameFilterName = SHOP_GET_DATA_FILTER::SHOP_NAME->value;
        $shopNameFilterType = FILTER_STRING_COMPARISON::STARTS_WITH->value;
        $shopNameFilterValue = str_pad('', 51, 'p');
        $page = 1;
        $pageItems = -1;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_filter_name={$shopNameFilterName}"
                ."&shop_name_filter_type={$shopNameFilterType}"
                ."&shop_name_filter_value={$shopNameFilterValue}"
                ."&page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['greater_than'], $responseContent->errors->page_items);
    }
}
