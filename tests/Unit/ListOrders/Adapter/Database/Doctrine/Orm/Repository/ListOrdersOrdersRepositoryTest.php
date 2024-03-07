<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Adapter\Database\Doctrine\Orm\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersOrdersRepository;
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersRepository;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use Order\Adapter\Database\Orm\Doctrine\Repository\OrderRepository;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class ListOrdersOrdersRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const LIST_ORDERS_EXISTS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const LIST_ORDERS_ORDERS_1 = '218ee820-fd4b-4a4c-abc3-0e26ac437dd9';
    private const LIST_ORDERS_ORDERS_2 = '36810398-3503-4ec4-9018-1f2607face0b';
    private const LIST_ORDERS_ORDERS_3 = '3fc1a0c4-2e17-480d-a423-d2c1c47b139e';
    private const ORDER_ID_1 = 'a0b4760a-9037-477a-8b84-d059ae5ee7e9';
    private const ORDER_ID_2 = '9a48ac5b-4571-43fd-ac80-28b08124ffb8';
    private const ORDER_ID_3 = 'd351adba-c566-4fa5-bb5b-1a6f73b1d72f';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersOrdersRepository $object;
    private OrderRepository $orderRepository;
    private ListOrdersRepository $listOrdersRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(ListOrdersOrders::class);
        $this->orderRepository = $this->entityManager->getRepository(Order::class);
        $this->listOrdersRepository = $this->entityManager->getRepository(ListOrders::class);
    }

    /**
     * @return ListOrdersOrders[]
     */
    private function getListOrdersOrdersNew(): array
    {
        $orders = $this->getOrdersExists();
        $listOrders = $this->getListOrdersExists();

        return [
            ListOrdersOrders::fromPrimitives(
                '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
                $orders[0]->getId()->getValue(),
                self::LIST_ORDERS_EXISTS_ID,
                false,
                $listOrders,
                $orders[0]
            ),
            ListOrdersOrders::fromPrimitives(
                'f96b600d-5033-4332-982e-edf238d12cf4',
                $orders[1]->getId()->getValue(),
                self::LIST_ORDERS_EXISTS_ID,
                false,
                $listOrders,
                $orders[1]
            ),
        ];
    }

    /**
     * @return ListOrdersOrders[]
     */
    private function getListOrdersOrdersExists(): array
    {
        return $this->object->findBy(['id' => [
            self::LIST_ORDERS_ORDERS_1,
            self::LIST_ORDERS_ORDERS_2,
            self::LIST_ORDERS_ORDERS_3,
        ]]);
    }

    private function getListOrdersExists(): ListOrders
    {
        return $this->listOrdersRepository->findBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_EXISTS_ID)])[0];
    }

    private function getOrdersExists(): array
    {
        return $this->orderRepository->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::ORDER_ID_1),
            ValueObjectFactory::createIdentifier(self::ORDER_ID_2),
            ValueObjectFactory::createIdentifier(self::ORDER_ID_3),
        ]]);
    }

    /** @test */
    public function itShouldSaveTheListOrdersOrdersInDatabase(): void
    {
        $listOrdersOrders = $this->getListOrdersOrdersNew();
        $listOrdersOrdersId = array_map(
            fn (ListOrdersOrders $listOrdersOrder) => $listOrdersOrder->getId(),
            $listOrdersOrders
        );

        $this->object->save($listOrdersOrders);

        /** @var Group $groupSaved */
        $listOrdersOrdersSaved = $this->object->findBy(['id' => $listOrdersOrdersId]);

        $this->assertSame($listOrdersOrders, $listOrdersOrdersSaved);
    }

    /** @test */
    public function itShouldFailSavingTheListOrdersOrdersInDatabase()
    {
        $listOrdersOrders = $this->getListOrdersOrdersNew();
        $listOrdersOrders[2] = clone $listOrdersOrders[1];

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save($listOrdersOrders);
    }

    /** @test */
    public function itShouldFailSavingDataBaseError(): void
    {
        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);

        $this->expectException(DBConnectionException::class);
        $this->object->save($this->getListOrdersOrdersNew());
    }

    /** @test */
    public function itShouldRemoveTheListOrdersOrders(): void
    {
        $listOrdersToRemove = $this->getListOrdersOrdersExists();
        $listOrdersToRemoveId = array_map(
            fn (ListOrdersOrders $listOrdersOrder) => $listOrdersOrder->getId(),
            $listOrdersToRemove
        );
        $this->object->remove($listOrdersToRemove);

        $orderRemoved = $this->object->findBy(['id' => $listOrdersToRemoveId]);

        $this->assertEmpty($orderRemoved);
    }

    /** @test */
    public function itShouldFailRemovingDataBaseError(): void
    {
        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);

        $this->expectException(DBConnectionException::class);
        $this->object->remove($this->getListOrdersOrdersExists());
    }

    /** @test */
    public function itShouldFindListsOrdersOrdersById(): void
    {
        $listOrdersOrdersExpected = array_filter(
            $this->getListOrdersOrdersExists(),
            fn (ListOrdersOrders $listOrdersOrders) => self::GROUP_ID === $listOrdersOrders->getOrder()->getGroupId()->getValue()
                                                    && (self::LIST_ORDERS_EXISTS_ID === $listOrdersOrders->getListOrders()->getId()->getValue())
        );

        $return = $this->object->findListOrderOrdersByIdOrFail([
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_EXISTS_ID),
        ],
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
        );

        $this->assertEquals($listOrdersOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailFindingListOrdersOrdersByIdGroupIdNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderOrdersByIdOrFail([
                ValueObjectFactory::createIdentifier(self::LIST_ORDERS_EXISTS_ID),
            ],
            ValueObjectFactory::createIdentifier('group id')
        );
    }

    /** @test */
    public function itShouldFailFindingListOrdersOrdersByIdListOrdersIdNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderOrdersByIdOrFail([
                ValueObjectFactory::createIdentifier('list orders id'),
            ],
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );
    }

    /** @test */
    public function itShouldFindListOrderOrdersByListOrderId(): void
    {
        $listOrderId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_EXISTS_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $listOrderOrdersDataPaginator = $this->object->findListOrderOrdersDataByIdOrFail($listOrderId, $groupId);
        $listOrderOrdersData = iterator_to_array($listOrderOrdersDataPaginator);

        $this->assertListOrdersOrdersAreOk($listOrderOrdersData);
    }

    /** @test */
    public function itShouldFailFindingListOrderOrdersByListOrderIdNotFound(): void
    {
        $listOrderId = ValueObjectFactory::createIdentifier('id not found');
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderOrdersDataByIdOrFail($listOrderId, $groupId);
    }

    /** @test */
    public function itShouldFailFindingListOrderOrdersByListOrderIdNotFoundInTheGroup(): void
    {
        $listOrderId = ValueObjectFactory::createIdentifier(self::LIST_ORDERS_EXISTS_ID);
        $groupId = ValueObjectFactory::createIdentifier('wrong id');

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderOrdersDataByIdOrFail($listOrderId, $groupId);
    }

    private function assertListOrdersOrdersAreOk(array $listOrderOrdersData): void
    {
        $this->assertCount(2, $listOrderOrdersData);
        $this->assertContainsOnlyInstancesOf(Order::class, $listOrderOrdersData);

        $this->assertEquals('a0b4760a-9037-477a-8b84-d059ae5ee7e9', $listOrderOrdersData[0]->getId());
        $this->assertEquals('8b6d650b-7bb7-4850-bf25-36cda9bce801', $listOrderOrdersData[0]->getProductId());
        $this->assertEquals('f6ae3da3-c8f2-4ccb-9143-0f361eec850e', $listOrderOrdersData[0]->getShopId());
        $this->assertEquals('2606508b-4516-45d6-93a6-c7cb416b7f3f', $listOrderOrdersData[0]->getUserId());
        $this->assertEquals('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', $listOrderOrdersData[0]->getGroupId());
        $this->assertEquals('order description 2', $listOrderOrdersData[0]->getDescription()->getValue());
        $this->assertEquals('20.050', $listOrderOrdersData[0]->getAmount()->getvalue());

        $this->assertEquals('9a48ac5b-4571-43fd-ac80-28b08124ffb8', $listOrderOrdersData[1]->getId());
        $this->assertEquals('afc62bc9-c42c-4c4d-8098-09ce51414a92', $listOrderOrdersData[1]->getProductId());
        $this->assertEquals('e6c1d350-f010-403c-a2d4-3865c14630ec', $listOrderOrdersData[1]->getShopId());
        $this->assertEquals('2606508b-4516-45d6-93a6-c7cb416b7f3f', $listOrderOrdersData[1]->getUserId());
        $this->assertEquals('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', $listOrderOrdersData[1]->getGroupId());
        $this->assertEquals('order description', $listOrderOrdersData[1]->getDescription()->getValue());
        $this->assertEquals('10.200', $listOrderOrdersData[1]->getAmount()->getvalue());
    }
}
