<?php

declare(strict_types=1);

namespace Test\Unit\Order\Adapter\Database\Orm\Doctrine\Repository;

use Override;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Order\Adapter\Database\Orm\Doctrine\Repository\OrderRepository;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Test\Unit\DataBaseTestCase;

class OrderRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_ID_2 = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const array ORDERS_ID = [
        '5cfe52e5-db78-41b3-9acd-c3c84924cb9b',
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
        '72f2f46d-3f3f-48d0-b4eb-5cbed7896cab',
    ];
    private const array PRODUCTS_AND_SHOPS_ID = [
        self::ORDERS_ID[0] => [
            'productId' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
            'shopId' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        ],
        self::ORDERS_ID[1] => [
            'productId' => 'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            'shopId' => 'e6c1d350-f010-403c-a2d4-3865c14630ec',
        ],
        self::ORDERS_ID[2] => [
            'productId' => '8b6d650b-7bb7-4850-bf25-36cda9bce801',
            'shopId' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        ],
        self::ORDERS_ID[3] => [
            'productId' => '7e3021d4-2d02-4386-8bbe-887cfe8697a8',
            'shopId' => 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        ],
        self::ORDERS_ID[4] => [
            'productId' => 'ca10c90a-c7e6-4594-89e9-71d2f5e74710',
            'shopId' => 'b9b1c541-d41e-4751-9ecb-4a1d823c0405',
        ],
    ];

    private OrderRepository $object;
    private ProductRepositoryInterface $productRepository;
    private ShopRepositoryInterface $shopRepository;
    private ListOrdersRepositoryInterface $listOrdersRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Order::class);
        $this->listOrdersRepository = $this->entityManager->getRepository(ListOrders::class);
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->shopRepository = $this->entityManager->getRepository(Shop::class);
    }

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'List order name 1',
            'List order description 1',
            null
        );
    }

    private function getNewProduct(): Product
    {
        return Product::fromPrimitives(
            'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            self::GROUP_ID,
            'product 1 name',
            'Product 1 description',
            null
        );
    }

    private function getNewShop(): Shop
    {
        return Shop::fromPrimitives(
            '8e5d2b03-ffa8-4ab0-b640-0cf994acff6d',
            self::GROUP_ID,
            'hop 1 name',
            'Shop 1 description',
            null
        );
    }

    private function getNewOrder(): Order
    {
        return Order::fromPrimitives(
            'b453b8c0-b84b-40c2-b4de-71d04eee3707',
            self::GROUP_ID,
            '741bdcf7-3c13-4c76-8c0c-98cb33933fb1',
            'Order description',
            35.75,
            false,
            $this->getListOrders(),
            $this->getNewProduct(),
            $this->getNewShop()
        );
    }

    private function getExistsOrder(): Order
    {
        return Order::fromPrimitives(
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
            '36810398-3503-4ec4-9018-1f2607face0b',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'order description',
            10.200,
            false,
            $this->getListOrders(),
            $this->getNewProduct()
        );
    }

    /** @test */
    public function itShouldSaveTheOrderInDatabase(): void
    {
        $orderNew = $this->getNewOrder();
        $listOrders = $this->listOrdersRepository->findOneby(['id' => ValueObjectFactory::createIdentifier('ba6bed75-4c6e-4ac3-8787-5bded95dac8d')]);
        $product = $this->productRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('afc62bc9-c42c-4c4d-8098-09ce51414a92')]);
        $shop = $this->shopRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('e6c1d350-f010-403c-a2d4-3865c14630ec')]);
        $orderNew->setListOrders($listOrders);
        $orderNew->setProduct($product);
        $orderNew->setShop($shop);

        $this->object->save([$orderNew]);

        /** @var Group $groupSaved */
        $orderSaved = $this->object->findOneBy(['id' => $orderNew->getId()]);

        $this->assertSame($orderNew, $orderSaved);
    }

    /** @test */
    public function itShouldFailOrderIdAlreadyExists(): void
    {
        $orderExists = $this->getExistsOrder();
        $listOrders = $this->listOrdersRepository->findOneby(['id' => ValueObjectFactory::createIdentifier('ba6bed75-4c6e-4ac3-8787-5bded95dac8d')]);
        $product = $this->productRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('afc62bc9-c42c-4c4d-8098-09ce51414a92')]);
        $shop = $this->shopRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('e6c1d350-f010-403c-a2d4-3865c14630ec')]);
        $orderExists->setListOrders($listOrders);
        $orderExists->setProduct($product);
        $orderExists->setShop($shop);

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save([$orderExists]);
    }

    /** @test */
    public function itShouldFailSavingDataBaseError(): void
    {
        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->save([$this->getNewOrder()]);
    }

    /** @test */
    public function itShouldRemoveTheOrder(): void
    {
        $order = $this->getExistsOrder();
        $orderToRemove = $this->object->findBy(['id' => $order->getId()]);
        $this->object->remove($orderToRemove);

        $orderRemoved = $this->object->findBy(['id' => $order->getId()]);

        $this->assertEmpty($orderRemoved);
    }

    /** @test */
    public function itShouldFailRemovingTheOrdersErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $order = $this->getNewOrder();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$order]);
    }

    /** @test */
    public function itShouldGetOrdersByIdAndGroupOrderAsc(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            'id' => [
                ValueObjectFactory::createIdentifier('9a48ac5b-4571-43fd-ac80-28b08124ffb8'),
                ValueObjectFactory::createIdentifier('a0b4760a-9037-477a-8b84-d059ae5ee7e9'),
            ],
        ];

        $expectedOrders = $this->object->findBy($ordersId);

        $ordersPaginator = $this->object->findOrdersByIdOrFail($groupId, $ordersId, $orderAsc);
        $ordersDb = iterator_to_array($ordersPaginator);

        $this->assertCount(count($ordersDb), $ordersDb);

        foreach ($ordersPaginator as $order) {
            $this->assertContainsEquals($order, $expectedOrders);
        }
    }

    /** @test */
    public function itShouldGetOrdersByIdAndGroupOrderDesc(): void
    {
        $orderAsc = false;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            'id' => [
                ValueObjectFactory::createIdentifier('a0b4760a-9037-477a-8b84-d059ae5ee7e9'),
                ValueObjectFactory::createIdentifier('9a48ac5b-4571-43fd-ac80-28b08124ffb8'),
            ],
        ];

        $expectedOrders = $this->object->findBy($ordersId);

        $ordersPaginator = $this->object->findOrdersByIdOrFail($groupId, $ordersId, $orderAsc);
        $ordersDb = iterator_to_array($ordersPaginator);

        $this->assertCount(count($ordersDb), $ordersDb);

        foreach ($ordersPaginator as $order) {
            $this->assertContainsEquals($order, $expectedOrders);
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersByIdAndGroupNotFound(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            'id' => [
                'f1f309b4-6afb-4733-b823-b31914be12bd',
                'e15586d2-24b2-47b4-97cc-508a4cb259ec',
            ],
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByIdOrFail($groupId, $ordersId, $orderAsc);
    }

    /** @test */
    public function itShouldFindOrdersByListName(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('List order name 1');

        $return = $this->object->findOrdersByListOrdersNameOrFail($groupId, $listOrdersName, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy(['listOrdersId' => ValueObjectFactory::createIdentifier('ba6bed75-4c6e-4ac3-8787-5bded95dac8d')]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFailFindOrdersByListNameGroupNotFound(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier('not valid group id');
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('List order name 1');

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByListOrdersNameOrFail($groupId, $listOrdersName, $orderAsc);
    }

    /** @test */
    public function itShouldFindOrdersByListOrdersIdAndProductName(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Juan Carlos')
        );

        $return = $this->object->findOrdersByProductNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy([
            'productId' => ValueObjectFactory::createIdentifier('8b6d650b-7bb7-4850-bf25-36cda9bce801'),
            'listOrdersId' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
        ]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByProductNameListOrdersIdIsNull(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(null);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Juan Carlos')
        );

        $return = $this->object->findOrdersByProductNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy(['productId' => ValueObjectFactory::createIdentifier('8b6d650b-7bb7-4850-bf25-36cda9bce801')]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByProductName(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = null;
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Juan Carlos')
        );

        $return = $this->object->findOrdersByProductNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy(['productId' => ValueObjectFactory::createIdentifier('8b6d650b-7bb7-4850-bf25-36cda9bce801')]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFailFindingOrdersByProductNameGroupNotFound(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = null;
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('product not exists')
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByProductNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
    }

    /** @test */
    public function itShouldFindOrdersByListOrdersIdAndShopName(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Shop name 2')
        );

        $return = $this->object->findOrdersByShopNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy([
            'shopId' => ValueObjectFactory::createIdentifier('f6ae3da3-c8f2-4ccb-9143-0f361eec850e'),
            'listOrdersId' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
        ]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByShopName(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = null;
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Shop name 2')
        );

        $return = $this->object->findOrdersByShopNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy([
            'shopId' => ValueObjectFactory::createIdentifier('f6ae3da3-c8f2-4ccb-9143-0f361eec850e'),
        ]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByShopNameListOrdersIdIsNull(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(null);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('Shop name 2')
        );

        $return = $this->object->findOrdersByShopNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy([
            'shopId' => ValueObjectFactory::createIdentifier('f6ae3da3-c8f2-4ccb-9143-0f361eec850e'),
        ]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByShopNameGroupNotFound(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = null;
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces('shops not exists')
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByShopNameFilterOrFail($groupId, $listOrdersId, $filterText, $orderAsc);
    }

    /** @test */
    public function itShouldFindOrdersByeGroupId(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findOrdersByGroupIdOrFail($groupId, $orderAsc);
        $ordersDb = iterator_to_array($return);

        $expectedOrders = $this->object->findBy(['groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID)]);

        $this->assertEqualsCanonicalizing($ordersDb, $expectedOrders);
    }

    /** @test */
    public function itShouldFindOrdersByGroupIdNotFound(): void
    {
        $orderAsc = true;
        $groupId = ValueObjectFactory::createIdentifier('group no found');

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByGroupIdOrFail($groupId, $orderAsc);
    }

    /** @test */
    public function itShouldGetOrdersByListOrdersIdProductsIdAndShopsId(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $productsId = array_map(
            fn (array $productAndShopId) => ValueObjectFactory::createIdentifier($productAndShopId['productId']),
            array_slice(self::PRODUCTS_AND_SHOPS_ID, 0, 3, false)
        );
        $shopId = array_map(
            fn (array $productAndShopId) => ValueObjectFactory::createIdentifier($productAndShopId['shopId']),
            array_slice(self::PRODUCTS_AND_SHOPS_ID, 0, 3, false)
        );

        $return = $this->object->findOrdersByListOrdersIdProductIdAndShopIdOrFail($groupId, $listOrdersId, $productsId, $shopId);
        $ordersExpected = $this->object->findBy(['id' => [self::ORDERS_ID[0], self::ORDERS_ID[1]]]);

        $this->assertEquals($ordersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetOrdersByListOrdersIdProductsIdAndShopsIdIsEqualsToNull(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $productsId = array_map(
            fn (array $productAndShopId) => ValueObjectFactory::createIdentifier($productAndShopId['productId']),
            self::PRODUCTS_AND_SHOPS_ID
        );
        $shopId = [];

        $return = $this->object->findOrdersByListOrdersIdProductIdAndShopIdOrFail($groupId, $listOrdersId, $productsId, $shopId);
        $ordersExpected = $this->object->findBy(['id' => [self::ORDERS_ID[5]]]);

        $this->assertEquals($ordersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailGettingOrdersByListOrdersIdProductsIdAndShopsIdNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $productsId = [
            ValueObjectFactory::createIdentifier('not found product id'),
        ];
        $shopId = [
            ValueObjectFactory::createIdentifier('not found shop id'),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByListOrdersIdProductIdAndShopIdOrFail($groupId, $listOrdersId, $productsId, $shopId);
    }

    /** @test */
    public function idShouldFindAllOrdersOfAListOfOrders(): void
    {
        $listOrdersId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $orderAsc = true;

        $return = $this->object->findOrdersByListOrdersIdOrFail($listOrdersId, $groupId, $orderAsc);

        $ordersExpected = $this->object->findBy([
            'listOrdersId' => $listOrdersId,
            'groupId' => $groupId,
        ]);

        $this->assertEqualsCanonicalizing($ordersExpected, iterator_to_array($return));
    }

    /** @test */
    public function idShouldFailFindingAllOrdersOfAListOfOrdersNotFound(): void
    {
        $listOrdersId = ValueObjectFactory::createIdentifier('id not found');
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $orderAsc = true;

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByListOrdersIdOrFail($listOrdersId, $groupId, $orderAsc);
    }

    /** @test */
    public function itShouldFindAllOrdersOfAGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ];

        $return = $this->object->findGroupsOrdersOrFail($groupsId);
        $expect = $this->object->findBy([
            'groupId' => $groupsId,
        ]);

        $this->assertEquals($expect, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailNoOrdersFoundInGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('6f76bd87-7382-46fc-9746-ba983dbbfeba'),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsOrdersOrFail($groupsId);
    }
}
