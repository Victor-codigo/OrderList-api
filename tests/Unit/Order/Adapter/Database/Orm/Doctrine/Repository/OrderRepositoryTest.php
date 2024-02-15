<?php

declare(strict_types=1);

namespace Test\Unit\Order\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
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

    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private OrderRepository $object;
    private ProductRepositoryInterface $productRepository;
    private ShopRepositoryInterface $shopRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Order::class);
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->shopRepository = $this->entityManager->getRepository(Shop::class);
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
            '741bdcf7-3c13-4c76-8c0c-98cb33933fb1',
            self::GROUP_ID,
            35.75,
            'Order description',
            $this->getNewProduct(),
            $this->getNewShop()
        );
    }

    private function getExistsOrder(): Order
    {
        return Order::fromPrimitives(
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            10.200,
            'Iure ut vero repellat quasi laboriosam earum enim iusto. Aut vitae perspiciatis ipsam et. Aspernatur voluptate voluptatem sequi inventore quas delectus. Quaerat ea repellat qui quia quasi pariatur velit. Tenetur officia iure fuga nihil. Dolorem temporibus voluptatem ut quia qui aut. Non aliquid excepturi architecto aut. Et dolorem quia expedita sint perspiciatis at. Qui quis ducimus error atque provident perspiciatis est. Distinctio suscipit qui aut officia eum sit recusandae.',
            $this->getNewProduct()
        );
    }

    /** @test */
    public function itShouldSaveTheOrderInDatabase(): void
    {
        $orderNew = $this->getNewOrder();
        $product = $this->productRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('afc62bc9-c42c-4c4d-8098-09ce51414a92')]);
        $shop = $this->shopRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('e6c1d350-f010-403c-a2d4-3865c14630ec')]);
        $orderNew->setProduct($product);
        $orderNew->setShop($shop);

        $this->object->save([$orderNew]);

        /** @var Group $groupSaved */
        $orderSaved = $this->object->findOneBy(['id' => $orderNew->getId()]);

        $this->assertSame($orderNew, $orderSaved);
    }

    /** @test */
    public function itShouldFailOrderIdAlreadyExists()
    {
        $orderExists = $this->getExistsOrder();
        $product = $this->productRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('afc62bc9-c42c-4c4d-8098-09ce51414a92')]);
        $shop = $this->shopRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier('e6c1d350-f010-403c-a2d4-3865c14630ec')]);
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
    public function itShouldGetOrdersByIdAndGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            'id' => [
                '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
                'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
            ],
        ];

        $expectedOrders = $this->object->findBy($ordersId);

        $ordersPaginator = $this->object->findOrdersByIdOrFail($ordersId, $groupId);
        $ordersDb = iterator_to_array($ordersPaginator);

        $this->assertCount(count($ordersDb), $ordersDb);

        foreach ($ordersPaginator as $order) {
            $this->assertContainsEquals($order, $expectedOrders);
        }
    }

    /** @test */
    public function itShouldGetOrdersById(): void
    {
        $ordersId = [
            'id' => [
                '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
                'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
            ],
        ];

        $expectedOrders = $this->object->findBy($ordersId);

        $ordersPaginator = $this->object->findOrdersByIdOrFail($ordersId);
        $ordersDb = iterator_to_array($ordersPaginator);

        $this->assertCount(count($ordersDb), $ordersDb);

        foreach ($ordersPaginator as $order) {
            $this->assertContainsEquals($order, $expectedOrders);
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersByIdAndGroupNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $ordersId = [
            'id' => [
                'f1f309b4-6afb-4733-b823-b31914be12bd',
                'e15586d2-24b2-47b4-97cc-508a4cb259ec',
            ],
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersByIdOrFail($ordersId, $groupId);
    }

    /** @test */
    public function itShouldFindOrdersOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findOrdersGroupOrFail($groupId);
        $ordersExpected = $this->object->findBy(['groupId' => $groupId]);

        $this->assertEquals($ordersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailFindingOrdersOfAGroupGroupNotExists(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('id group not exists');

        $this->expectException(DBNotFoundException::class);
        $this->object->findOrdersGroupOrFail($groupId);
    }
}
