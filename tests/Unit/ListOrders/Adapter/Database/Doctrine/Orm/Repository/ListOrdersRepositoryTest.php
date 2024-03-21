<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Adapter\Database\Doctrine\Orm\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
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
    public function itShouldRemoveTheListOrders22(): void
    {
        $listOrdersExists = $this->getListOrdersExists();
        $listOrdersToRemove = $this->object->findBy(['id' => $listOrdersExists->getId()]);
        $this->object->remove($listOrdersToRemove);

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
        $this->object->remove([$this->getListOrders()]);
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
    public function itShouldFindListOrdersOfAGroup(): void
    {
        $return = $this->object->findListOrdersGroup(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            true
        );

        $listOrdersExpected = $this->object->findBy(['id' => self::LIST_ORDERS_ID]);
        $listOrdersActual = iterator_to_array($return);

        $this->assertCount(count($listOrdersExpected), $listOrdersActual);

        foreach ($listOrdersActual as $listOrder) {
            $this->assertContainsEquals($listOrder, $listOrdersExpected);
        }
    }

    /** @test */
    public function itShouldFailFindingListOrdersOfAGroupNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrdersGroup(
            ValueObjectFactory::createIdentifier('not found id'),
            true
        );
    }

    /** @test */
    public function itShouldFindListOrdersByListOrdersNameFilterEquals(): void
    {
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('List order name 2');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $listOrdersName
        );
        $return = $this->object->findListOrderByListOrdersNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['name' => $listOrdersName], ['name' => 'asc']);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByListOrdersNameFilterStartsWith(): void
    {
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('List order');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            $listOrdersName
        );
        $return = $this->object->findListOrderByListOrdersNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            false
        );
        $listOrdersExpected = $this->object->findBy(['name' => [
            ValueObjectFactory::createNameWithSpaces('List order name 4'),
            ValueObjectFactory::createNameWithSpaces('List order name 3'),
            ValueObjectFactory::createNameWithSpaces('List order name 2'),
            ValueObjectFactory::createNameWithSpaces('List order name 1'),
        ]],
            ['name' => 'desc']
        );

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByListOrdersNameFilterEndsWith(): void
    {
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('name 3');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
            $listOrdersName
        );
        $return = $this->object->findListOrderByListOrdersNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy([
            'name' => ValueObjectFactory::createNameWithSpaces('List order name 3'),
        ], [
            'name' => 'asc',
        ]
        );

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByListOrdersNameFilterContains(): void
    {
        $listOrdersName = ValueObjectFactory::createNameWithSpaces('name');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            $listOrdersName
        );
        $return = $this->object->findListOrderByListOrdersNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            false
        );
        $listOrdersExpected = $this->object->findBy(['name' => [
            ValueObjectFactory::createNameWithSpaces('List order name 4'),
            ValueObjectFactory::createNameWithSpaces('List order name 3'),
            ValueObjectFactory::createNameWithSpaces('List order name 2'),
            ValueObjectFactory::createNameWithSpaces('List order name 1'),
        ]],
            ['name' => 'desc']
        );

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByProductsNameFilterEquals(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Juan Carlos');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $productName
        );
        $return = $this->object->findListOrderByProductNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[1]),
        ]]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByProductsNameFilterStartsWith(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Peri');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            $productName
        );
        $return = $this->object->findListOrderByProductNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[3])]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByProductsNameFilterEndsWith(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Carlos');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
            $productName
        );
        $return = $this->object->findListOrderByProductNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );

        $listOrdersExpected = $this->object->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[1]),
        ]]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByProductsNameFilterContains(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Carl');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            $productName
        );
        $return = $this->object->findListOrderByProductNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );

        $listOrdersExpected = $this->object->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[1]),
        ]]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailFindListOrdersByProductsNameFilterContainsNotFound(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('not found');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            $productName
        );
        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByProductNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
    }

    /** @test */
    public function itShouldFindListOrdersByShopsNameFilterEquals(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Shop name 1');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            $productName
        );
        $return = $this->object->findListOrderByShopNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0])]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByShopsNameFilterStartsWith(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Shop name');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            $productName
        );
        $return = $this->object->findListOrderByShopNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[1]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[3]),
        ]]);
        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByShopsNameFilterEndsWith(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('name 1');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
            $productName
        );
        $return = $this->object->findListOrderByShopNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0])]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFindListOrdersByShopsNameFilterContains(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('name');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            $productName
        );
        $return = $this->object->findListOrderByShopNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
        $listOrdersExpected = $this->object->findBy(['id' => [
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[0]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[1]),
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID[3]),
        ]]);

        $this->assertEquals($listOrdersExpected, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailFindListOrdersByShopsNameFilterContainsNotFound(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('not found');
        $filterText = ValueObjectFactory::createFilter(
            'filter_text',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            $productName
        );
        $this->expectException(DBNotFoundException::class);
        $this->object->findListOrderByShopNameFilterOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $filterText,
            true
        );
    }
}
