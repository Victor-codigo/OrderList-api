<?php

declare(strict_types=1);

namespace Test\Functional\Shop\Adapter\Http\Controller\ShopGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
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
        $shopNameStartsWith = 'Sho';
        $orderAsc = true;

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
                ."&shop_name_starts_with={$shopNameStartsWith}"
                ."&order_asc={$orderAsc}",
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
                ."&shops_id={$shopsId}",
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
                ."&order_asc={$orderArc}",
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
                ."&shop_name={$shopName}",
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
    public function itShouldGetShopsShopNameStartsWithOrderDesc(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopNameStartsWith = 'Sho';
        $orderAsc = false;

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
                ."&shop_name_starts_with={$shopNameStartsWith}"
                ."&order_asc={$orderAsc}",
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
                ."&products_id={$productsId}",
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
        $shopNameStartsWith = 'Shop name 2';

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
                ."&shop_name={$shopNameStartsWith}",
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
        $shopNameStartsWith = 'Sho';
        $orderAsc = true;

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
                ."&shop_name_starts_with={$shopNameStartsWith}"
                ."&order_asc={$orderAsc}",
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
    public function itShouldFailNoShopsFoundWinShopName(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopName = 'shop name not exists';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shop_name={$shopName}",
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
        $shopNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
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
        $shopNameStartsWith = 'Sho';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
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
        $shopNameStartsWith = 'Sho';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
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
    public function itShouldFailProducstIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', ['wong id']);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
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
        $shopNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
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

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name={$shopName}",
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
    public function itShouldFailShopNameStartsWithIsToolLong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopNameStartsWith = str_pad('', 51, 'p');

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                ."?group_id={$groupId}"
                ."&shops_id={$shopsId}"
                ."&products_id={$productsId}"
                ."&shop_name_starts_with={$shopNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_starts_with'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['string_too_long'], $responseContent->errors->shop_name_starts_with);
    }
}
