<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Repository\Paginator;

use Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\DoctrinePaginatorAdapter;
use Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Exception\PaginatorPageException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use DG\BypassFinals;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Fixtures\QueryResult;

class DoctrinePaginatorAdapterTest extends TestCase
{
    private MockObject|Connection $connection;
    private MockObject|EntityManager $entityManager;
    private MockObject|AbstractPlatform $abstractPlatform;

    /**
     * @var QueryResult[]
     */
    private array $queryResult;

    protected function setUp(): void
    {
        parent::setUp();

        BypassFinals::enable();

        $this->connection = $this->createMock(Connection::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->abstractPlatform = $this->createMock(AbstractPlatform::class);
        $this->queryResult = $this->getQueryResult();

        $this->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->abstractPlatform);
    }

    private function getQueryResult(): array
    {
        return [
            new QueryResult('1', 'name 1', 15),
            new QueryResult('2', 'name 2', 16),
            new QueryResult('3', 'name 3', 35),
            new QueryResult('4', 'name 4', 31),
            new QueryResult('5', 'name 5', 12),
            new QueryResult('6', 'name 6', 46),
            new QueryResult('7', 'name 7', 23),
            new QueryResult('8', 'name 8', 27),
            new QueryResult('9', 'name 9', 18),
            new QueryResult('10', 'name 10', 43),
            new QueryResult('11', 'name 11', 19),
            new QueryResult('12', 'name 12', 20),
            new QueryResult('13', 'name 13', 56),
            new QueryResult('14', 'name 14', 65),
            new QueryResult('15', 'name 15', 44),
            new QueryResult('16', 'name 16', 78),
            new QueryResult('17', 'name 17', 79),
            new QueryResult('18', 'name 18', 80),
            new QueryResult('19', 'name 19', 19),
            new QueryResult('20', 'name 20', 21),
        ];
    }

    /**
     * @param QueryResult[] $queryPageResult
     */
    private function mockPaginator(Query $query, array $queryResult): MockObject|Paginator
    {
        /** @var MockObject|Paginator $paginator */
        $paginator = $this->createMock(Paginator::class);

        $paginator
            ->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $paginator
            ->expects($this->any())
            ->method('count')
            ->willReturn(count($this->queryResult));

        return $paginator;
    }

    private function createObjectTest(Query $query, Paginator $paginator): DoctrinePaginatorAdapter
    {
        $object = new DoctrinePaginatorAdapter($query);

        $objectReflection = new \ReflectionClass($object);
        $paginatorProperty = $objectReflection->getProperty('paginator');
        $paginatorProperty->setAccessible(true);
        $paginatorProperty->setValue($object, $paginator);

        return $object;
    }

    /**
     * @param QueryResult[] $queryPageResult
     */
    private function mockQuery(array $queryResult, EntityManager $entityManager, int $pageItems): MockObject|Query
    {
        /** @var MockObject|Query */
        $query = $this->createMock(Query::class);

        $query
            ->expects($this->any())
            ->method('getParameters')
            ->willReturn(new ArrayCollection());

        $query
            ->expects($this->any())
            ->method('getHints')
            ->willReturn([]);

        $query
            ->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $query
            ->expects($this->any())
            ->method('getScalarResult')
            ->willReturn($this->queryResult);

        $query
            ->expects($this->any())
            ->method('getMaxResults')
            ->willReturn($pageItems);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        return $query;
    }

    private function mockObjects(int $pageItems): DoctrinePaginatorAdapter
    {
        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);

        return $this->createObjectTest($query, $paginator);
    }

    /** @test */
    public function itShouldCreateAPaginator(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $object = new DoctrinePaginatorAdapter();

        $return = $object->createPaginator($query);

        $objectReflection = new \ReflectionClass($return);
        $paginatorProperty = $objectReflection->getProperty('paginator');
        $paginatorProperty->setAccessible(true);
        $paginator = $paginatorProperty->getValue($return);

        $this->assertInstanceOf(DoctrinePaginatorAdapter::class, $return);
        $this->assertNotSame($object, $return);
        $this->assertInstanceOf(Paginator::class, $paginator);
        $this->assertSame($query, $paginator->getQuery());
    }

    /** @test */
    public function itShouldGetPageItems(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getMaxResults')
            ->willReturn($pageItems);

        $return = $object->getPageItems();

        $this->assertEquals($pageItems, $return);
    }

    /** @test */
    public function itShouldFailGetPageItemsQueryNotSet(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $object = new DoctrinePaginatorAdapter();

        $query
            ->expects($this->any())
            ->method('getMaxResults')
            ->willReturn($pageItems);

        $this->expectException(LogicException::class);
        $object->getPageItems();
    }

    /** @test */
    public function itShouldSetPaginateResultPageOne(): void
    {
        $pageItems = 5;
        $page = 1;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $query
            ->expects($this->any())
            ->method('setFirstResult')
            ->with(0);

        $return = $object->setPagination($page, $pageItems);

        $this->assertEquals($object, $return);
    }

    /** @test */
    public function itShouldSetPaginateResultPageTwo(): void
    {
        $pageItems = 5;
        $page = 2;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $query
            ->expects($this->any())
            ->method('setFirstResult')
            ->with(5);

        $return = $object->setPagination($page, $pageItems);

        $this->assertEquals($object, $return);
    }

    /** @test */
    public function itShouldSetPaginateResultPageThree(): void
    {
        $pageItems = 5;
        $page = 3;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $query
            ->expects($this->any())
            ->method('setFirstResult')
            ->with(10);

        $return = $object->setPagination($page, $pageItems);

        $this->assertEquals($object, $return);
    }

    /** @test */
    public function itShouldSetPaginateResultPageFour(): void
    {
        $pageItems = 5;
        $page = 4;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $query
            ->expects($this->any())
            ->method('setFirstResult')
            ->with(15);

        $return = $object->setPagination($page, $pageItems);

        $this->assertEquals($object, $return);
    }

    /** @test */
    public function itShouldFailSettingPaginationPageItemsLowerThanOne(): void
    {
        $pageItems = 0;
        $page = 4;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $this->expectException(InvalidArgumentException::class);
        $object->setPagination($page, $pageItems);
    }

    /** @test */
    public function itShouldFailSettingPaginationPageLowerThanOne(): void
    {
        $pageItems = 5;
        $page = 0;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('setMaxResults')
            ->with($pageItems);

        $this->expectException(PaginatorPageException::class);
        $object->setPagination($page, $pageItems);
    }

     /** @test */
     public function itShouldSetPaginationPageGreaterThanTotalPagesToLastPage(): void
     {
         $pageItems = 5;
         $page = 5;

         $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
         $paginator = $this->mockPaginator($query, $this->queryResult);
         $object = $this->createObjectTest($query, $paginator);

         $query
             ->expects($this->any())
             ->method('setMaxResults')
             ->with($pageItems);

         $query
            ->expects($this->any())
            ->method('setFirstResult')
            ->with($pageItems * (4 - 1));

         $return = $object->setPagination($page, $pageItems);

         $this->assertInstanceOf(DoctrinePaginatorAdapter::class, $return);
     }

    /** @test */
    public function itShouldFailGettingPageCurrentPageQueryNotSet(): void
    {
        $object = new DoctrinePaginatorAdapter();

        $this->expectException(LogicException::class);
        $object->getPageCurrent();
    }

    public function itShouldGetPageCurrentPageOne(): void
    {
        $pageItems = 5;
        $page = 1;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(0);

        $return = $object->getPageCurrent();

        $this->assertEquals($page, $return);
    }

    /** @test */
    public function itShouldGetPageCurrentPageTwo(): void
    {
        $pageItems = 5;
        $page = 2;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(5);

        $return = $object->getPageCurrent();

        $this->assertEquals($page, $return);
    }

    /** @test */
    public function itShouldGetPageCurrentPageThree(): void
    {
        $pageItems = 5;
        $page = 3;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(10);

        $return = $object->getPageCurrent();

        $this->assertEquals($page, $return);
    }

    /** @test */
    public function itShouldGetPageCurrentPageFour(): void
    {
        $pageItems = 5;
        $page = 4;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(15);

        $return = $object->getPageCurrent();

        $this->assertEquals($page, $return);
    }

    /** @test */
    public function itShouldGetPagesTotal(): void
    {
        $pageItems = 5;

        $object = $this->mockObjects($pageItems);

        $return = $object->getPagesTotal();

        $this->assertEquals(4, $return);
    }

    /** @test */
    public function itShouldGetPagesTotalResultEmpty(): void
    {
        $pageItems = 5;
        $this->queryResult = [];

        $object = $this->mockObjects($pageItems);

        $return = $object->getPagesTotal();

        $this->assertEquals(1, $return);
    }

    /** @test */
    public function itShouldFailGettingPagesTotalPageQueryNotSet(): void
    {
        $object = new DoctrinePaginatorAdapter();

        $this->expectException(LogicException::class);
        $object->getPagesTotal();
    }

    /** @test */
    public function itShouldHasNextPage(): void
    {
        $pageItems = 5;

        $object = $this->mockObjects($pageItems);

        $object->setPagination(3, $pageItems);
        $return = $object->hasNext();

        $this->assertTrue($return);
    }

   /** @test */
   public function itShouldHasNotNextPage(): void
   {
       $pageItems = 5;

       $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
       $paginator = $this->mockPaginator($query, $this->queryResult);
       $object = $this->createObjectTest($query, $paginator);

       $query
           ->expects($this->any())
           ->method('getFirstResult')
           ->willReturn(15);

       $object->setPagination(4, $pageItems);
       $return = $object->hasNext();

       $this->assertFalse($return);
   }

   /** @test */
   public function itShouldFailHasNextPageQueryNotSet(): void
   {
       $object = new DoctrinePaginatorAdapter();

       $this->expectException(LogicException::class);
       $object->hasNext();
   }

    /** @test */
    public function itShouldHasPrevious(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(5);

        $object->setPagination(2, $pageItems);
        $return = $object->hasPrevious();

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldHasNotPrevious(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(0);

        $object->setPagination(1, $pageItems);
        $return = $object->hasPrevious();

        $this->assertFalse($return);
    }

   /** @test */
   public function itShouldFailHasPreviousQueryNotSet(): void
   {
       $object = new DoctrinePaginatorAdapter();

       $this->expectException(LogicException::class);
       $object->hasPrevious();
   }

    /** @test */
    public function itShouldGetPageNextNumber(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(0);

        $return = $object->getPageNextNumber();

        $this->assertEquals(2, $return);
    }

    /** @test */
    public function itShouldNotGetPageNextNumber(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(15);

        $return = $object->getPageNextNumber();

        $this->assertNull($return);
    }

   /** @test */
   public function itShouldFailGettingPageNextNumberQueryNotSet(): void
   {
       $object = new DoctrinePaginatorAdapter();

       $this->expectException(LogicException::class);
       $object->getPageNextNumber();
   }

    /** @test */
    public function itShouldGetPagePreviousNumber(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(15);

        $return = $object->getPagePreviousNumber();

        $this->assertEquals(3, $return);
    }

    /** @test */
    public function itShouldNotGetPagePreviousNumber(): void
    {
        $pageItems = 5;

        $query = $this->mockQuery($this->queryResult, $this->entityManager, $pageItems);
        $paginator = $this->mockPaginator($query, $this->queryResult);
        $object = $this->createObjectTest($query, $paginator);

        $query
            ->expects($this->any())
            ->method('getFirstResult')
            ->willReturn(0);

        $return = $object->getPagePreviousNumber();

        $this->assertNull($return);
    }

   /** @test */
   public function itShouldFailGettingPagePreviousNumberQueryNotSet(): void
   {
       $object = new DoctrinePaginatorAdapter();

       $this->expectException(LogicException::class);
       $object->getPagePreviousNumber();
   }
}
