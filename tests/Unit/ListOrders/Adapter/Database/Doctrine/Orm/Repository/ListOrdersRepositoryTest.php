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
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersRepository;
use ListOrders\Domain\Model\ListOrders;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class ListOrdersRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const LIST_ORDERS_EXISTS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const LIST_ORDERS_ID = [
        'ba6bed75-4c6e-4ac3-8787-5bded95dac8d',
        'd446eab9-5199-48d0-91f5-0407a86bcb4f',
        'f1559a23-2f92-4660-a335-b1052d7395da',
        'f2980f67-4eb9-41ca-b452-ffa2c7da6a37',
    ];
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersRepository $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(ListOrders::class);
    }

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

    /** @test */
    public function itShouldFindListOrdersById(): void
    {
        $return = $this->object->findListOrderByIdOrFail(self::LIST_ORDERS_ID);

        $listOrdersExpected = $this->object->findBy(['id' => self::LIST_ORDERS_ID]);
        $listOrdersActual = iterator_to_array($return);

        $this->assertCount(count($listOrdersExpected), $listOrdersActual);

        foreach ($listOrdersActual as $listOrder) {
            $this->assertContainsEquals($listOrder, $listOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFindListOrdersByIdGroupId(): void
    {
        $return = $this->object->findListOrderByIdOrFail(
            self::LIST_ORDERS_ID,
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );

        $listOrdersExpected = $this->object->findBy(['id' => self::LIST_ORDERS_ID]);
        $listOrdersActual = iterator_to_array($return);

        $this->assertCount(count($listOrdersExpected), $listOrdersActual);

        foreach ($listOrdersActual as $listOrder) {
            $this->assertContainsEquals($listOrder, $listOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFailFindingListOrdersByIdNotFound(): void
    {
        $listOrdersId = '28fbc151-06eb-4d98-8479-12188432f5d8';

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByIdOrFail([$listOrdersId]);
    }

    /** @test */
    public function itShouldFailFindingListOrdersByIdGroupIdNotFound(): void
    {
        $listOrdersId = self::LIST_ORDERS_ID;
        $groupId = ValueObjectFactory::createIdentifier('not found id');

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByIdOrFail($listOrdersId, $groupId);
    }

    /** @test */
    public function itShouldFindListOrdersByNameStarsWith(): void
    {
        $return = $this->object->findListOrderByNameStarsWithOrFail(
            ValueObjectFactory::createNameWithSpaces('List')
        );

        $listOrdersExpected = $this->object->findBy(['id' => self::LIST_ORDERS_ID]);
        $listOrdersActual = iterator_to_array($return);

        $this->assertCount(count($listOrdersExpected), $listOrdersActual);

        foreach ($listOrdersActual as $listOrder) {
            $this->assertContainsEquals($listOrder, $listOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFindListOrdersByNameStarsWithGroupId22(): void
    {
        $return = $this->object->findListOrderByNameStarsWithOrFail(
            ValueObjectFactory::createNameWithSpaces('List'),
            ValueObjectFactory::createIdentifier(self::GROUP_ID)
        );

        $listOrdersExpected = $this->object->findBy(['id' => self::LIST_ORDERS_ID]);
        $listOrdersActual = iterator_to_array($return);

        $this->assertCount(count($listOrdersExpected), $listOrdersActual);

        foreach ($listOrdersActual as $listOrder) {
            $this->assertContainsEquals($listOrder, $listOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFailFindListOrdersByNameStarsWithNotFound(): void
    {
        $nameStartsWith = ValueObjectFactory::createNameWithSpaces('not found');

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByNameStarsWithOrFail($nameStartsWith);
    }

    /** @test */
    public function itShouldFailFindListOrdersByNameStarsWithGroupIdNotFound(): void
    {
        $nameStartsWith = ValueObjectFactory::createNameWithSpaces('not found');
        $groupId = ValueObjectFactory::createIdentifier('not found id');

        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByNameStarsWithOrFail($nameStartsWith, $groupId);
    }
}
