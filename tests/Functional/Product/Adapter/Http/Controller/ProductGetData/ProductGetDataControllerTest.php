<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductGetDataControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/products';
    private const METHOD = 'GET';
    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRODUCT_EXISTS_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const SHOP_EXISTS_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';

    private function assertProductDataIsOk(array $productsDataExpected, object $productDataActual): void
    {
        $this->assertTrue(property_exists($productDataActual, 'id'));
        $this->assertTrue(property_exists($productDataActual, 'group_id'));
        $this->assertTrue(property_exists($productDataActual, 'name'));
        $this->assertTrue(property_exists($productDataActual, 'description'));
        $this->assertTrue(property_exists($productDataActual, 'image'));
        $this->assertTrue(property_exists($productDataActual, 'created_on'));

        $this->assertContainsEquals(
            $productDataActual->id,
            array_map(
                fn (array $product) => $product['id'],
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual->group_id,
            array_map(
                fn (array $product) => $product['group_id'],
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual->name,
            array_map(
                fn (array $product) => $product['name'],
                $productsDataExpected
            )
        );
        $this->assertContainsEquals(
            $productDataActual->image, array_map(
                fn (array $product) => $product['image'],
                $productsDataExpected
            )
        );
        $this->assertIsString($productDataActual->created_on);
    }

    /** @test */
    public function itShouldGetProducts(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $productDataExpected = [
            [
                'id' => self::PRODUCT_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldGetProductsWithProduct(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);

        $productDataExpected = [
            [
                'id' => self::PRODUCT_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldGetProductsWithShops(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);

        $productDataExpected = [
            [
                'id' => self::PRODUCT_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
            [
                'id' => '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Juanola',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&shops_id={$shopsId}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldGetProductsProductNameStartsWith(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productNameStartsWith = 'Ju';

        $productDataExpected = [
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
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&product_name_starts_with={$productNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldGetProductsWithProductsAndShops(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);

        $productDataExpected = [
            [
                'id' => self::PRODUCT_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldGetProductsWithShopsAndProductNameStartsWith(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $productDataExpected = [
            [
                'id' => self::PRODUCT_EXISTS_ID,
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'Dolorem omnis accusamus iusto qui rerum eligendi. Ipsa omnis autem totam est vero qui. Voluptas quisquam cumque dolorem ut debitis recusandae veniam. Quam repellendus est sed enim doloremque eum eius. Ut est odio est. Voluptates dolorem et nisi voluptatum. Voluptas vitae deserunt mollitia consequuntur eos. Suscipit recusandae hic cumque voluptatem officia. Exercitationem quibusdam ea qui laudantium est non quis. Vero dicta et voluptas explicabo.',
                'image' => null,
                'created_on' => '',
            ],
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Products data', $responseContent->message);

        foreach ($responseContent->data as $productData) {
            $this->assertProductDataIsOk($productDataExpected, $productData);
        }
    }

    /** @test */
    public function itShouldFailNoProductsFound(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', ['f7d3f835-9c72-4dda-821c-73e4f3e878a5']);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailNoPersmissionsInTheGroup(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
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
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
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
        $productsId = implode(',', ['wong id']);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
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
    public function itShouldFailShopsIdIsWrong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', ['wrong id']);
        $productNameStartsWith = 'Ma';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
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
    public function itShouldFailProductNameStartsWithIsToolLong(): void
    {
        $groupId = self::GROUP_EXISTS_ID;
        $productsId = implode(',', [self::PRODUCT_EXISTS_ID]);
        $shopsId = implode(',', [self::SHOP_EXISTS_ID]);
        $productNameStartsWith = str_pad('', 51, 'p');

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."?group_id={$groupId}&products_id={$productsId}&shops_id={$shopsId}&product_name_starts_with={$productNameStartsWith}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_starts_with'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['string_too_long'], $responseContent->errors->product_name_starts_with);
    }
}
