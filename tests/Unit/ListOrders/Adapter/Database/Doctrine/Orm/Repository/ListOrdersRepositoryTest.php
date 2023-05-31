<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Adapter\Database\Doctrine\Orm\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersRepository;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class ListOrdersRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const LIST_ORDERS_EXISTS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersRepository $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(ListOrders::class);
    }

    // private function getNewOrder(): Order
    // {
    //     return Order::fromPrimitives(
    //         'b453b8c0-b84b-40c2-b4de-71d04eee3707',
    //         '741bdcf7-3c13-4c76-8c0c-98cb33933fb1',
    //         self::GROUP_ID,
    //         35.75,
    //         'Order description',
    //         $this->getNewProduct(),
    //         $this->getNewShop()
    //     );
    // }

    // private function getExistsOrder(): Order
    // {
    //     return Order::fromPrimitives(
    //         '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
    //         '2606508b-4516-45d6-93a6-c7cb416b7f3f',
    //         '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
    //         10.200,
    //         'Iure ut vero repellat quasi laboriosam earum enim iusto. Aut vitae perspiciatis ipsam et. Aspernatur voluptate voluptatem sequi inventore quas delectus. Quaerat ea repellat qui quia quasi pariatur velit. Tenetur officia iure fuga nihil. Dolorem temporibus voluptatem ut quia qui aut. Non aliquid excepturi architecto aut. Et dolorem quia expedita sint perspiciatis at. Qui quis ducimus error atque provident perspiciatis est. Distinctio suscipit qui aut officia eum sit recusandae.',
    //         $this->getNewProduct()
    //     );
    // }

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
            self::GROUP_ID,
            self::USER_ID,
            'listOrders name',
            'listOrders description',
            new \DateTime()
        );
    }

    private function getListOrdersExists(): ListOrders
    {
        return ListOrders::fromPrimitives(
            self::LIST_ORDERS_EXISTS_ID,
            self::GROUP_ID,
            self::USER_ID,
            'listOrders name',
            'listOrders description',
            new \DateTime()
        );
    }

    /** @test */
    public function itShouldSaveTheListOrdersInDatabase(): void
    {
        $listOrders = $this->getListOrders();

        $this->object->save($listOrders);

        /** @var Group $groupSaved */
        $listOrdersSaved = $this->object->findOneBy(['id' => $listOrders->getId()]);

        $this->assertSame($listOrders, $listOrdersSaved);
    }

    /** @test */
    public function itShouldFailListOrdersIdAlreadyExists()
    {
        $listOrders = $this->getListOrdersExists();

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save($listOrders);
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
        $this->object->save($this->getListOrders());
    }

    /** @test */
    public function itShouldRemoveTheListOrders(): void
    {
        $listOrdersExists = $this->getListOrdersExists();
        $listOrdersToRemove = $this->object->findBy(['id' => $listOrdersExists->getId()]);
        $this->object->remove($listOrdersToRemove[0]);

        $orderRemoved = $this->object->findBy(['id' => $listOrdersExists->getId()]);

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
        $this->object->remove($this->getListOrders());
    }
}
