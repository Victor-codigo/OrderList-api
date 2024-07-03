<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class RepositoryBaseTest extends DataBaseTestCase
{
    private RepositoryBase $object;
    private MockObject|ManagerRegistry $managerRegistry;
    protected MockObject|EntityManagerInterface $entityManagerMock;
    private MockObject|ObjectManager $objectManager;
    private MockObject|ClassMetadata $classMetadata;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->objectManager);

        $this->managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetadata);

        $this->object = $this
            ->getMockBuilder(RepositoryBase::class)
            ->setConstructorArgs([$this->managerRegistry, 'entityClass', $this->paginator])
            ->getMockForAbstractClass();
    }

    private function invokeProtectedMethod(object $object, string $name, array $args = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    private function assertQueryIsOk(Query|QueryBuilder $queryExpected, Query|QueryBuilder $queryActual): void
    {
        $this->assertEquals($queryExpected->getDQL(), $queryActual->getDQL());
        $this->assertEquals($queryExpected->getParameters(), $queryActual->getParameters());
    }

    /** @test */
    public function itShouldCreateAPaginatorWithADqlAndItsParameters(): void
    {
        $dql = 'SELECT * FROM Users';
        $dqlParameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $queryExpected = new Query($this->entityManager);
        $queryExpected->setDQL($dql);
        $queryExpected->setParameters($dqlParameters);

        $queryEntityManager = clone $queryExpected;

        $this->entityManagerMock
            ->expects($this->once())
            ->method('createQuery')
            ->with($dql)
            ->willReturn($queryEntityManager);

        $this->paginator
            ->expects($this->once())
            ->method('createPaginator')
            ->with($this->callback(fn (Query $queryActual): true => $this->assertQueryIsOk($queryExpected, $queryActual) || true))
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getItemsTotal')
            ->willReturn(1);

        $return = $this->invokeProtectedMethod($this->object, 'dqlPaginationOrFail', [
            $dql,
            $dqlParameters,
        ]);

        $this->assertEquals($this->paginator, $return);
    }

    /** @test */
    public function itShouldCreateAPaginatorWithADqlWithoutParameters(): void
    {
        $dql = 'SELECT * FROM Users';

        $queryExpected = new Query($this->entityManager);
        $queryExpected->setDQL($dql);

        $queryEntityManager = clone $queryExpected;

        $this->entityManagerMock
            ->expects($this->once())
            ->method('createQuery')
            ->with($dql)
            ->willReturn($queryEntityManager);

        $this->paginator
            ->expects($this->once())
            ->method('createPaginator')
            ->with($this->callback(fn (Query $queryActual): true => $this->assertQueryIsOk($queryExpected, $queryActual) || true))
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getItemsTotal')
            ->willReturn(1);

        $return = $this->invokeProtectedMethod($this->object, 'dqlPaginationOrFail', [
            $dql,
        ]);

        $this->assertEquals($this->paginator, $return);
    }

    /** @test */
    public function itShouldFailCreatingAPaginatorWithADqlAndItsParametersNotFound(): void
    {
        $dql = 'SELECT * FROM Users';
        $dqlParameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $queryExpected = new Query($this->entityManager);
        $queryExpected->setDQL($dql);
        $queryExpected->setParameters($dqlParameters);

        $queryEntityManager = clone $queryExpected;

        $this->entityManagerMock
            ->expects($this->once())
            ->method('createQuery')
            ->with($dql)
            ->willReturn($queryEntityManager);

        $this->paginator
            ->expects($this->once())
            ->method('createPaginator')
            ->with($this->callback(fn (Query $queryActual): true => $this->assertQueryIsOk($queryExpected, $queryActual) || true))
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getItemsTotal')
            ->willReturn(0);

        $this->expectException(DBNotFoundException::class);
        $this->invokeProtectedMethod($this->object, 'dqlPaginationOrFail', [
            $dql,
            $dqlParameters,
        ]);
    }
}
