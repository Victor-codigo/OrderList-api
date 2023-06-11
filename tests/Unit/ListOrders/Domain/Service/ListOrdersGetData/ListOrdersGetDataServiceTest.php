<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;
use ListOrders\Domain\Service\ListOrdersGetData\ListOrdersGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersGetDataServiceTest extends TestCase
{
    private ListOrdersGetDataService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersGetDataService($this->listOrdersRepository);
    }

    private function getListOrders(): array
    {
        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', '2023-6-9 18:06:00');

        return [
            'list orders id 1' => ListOrders::fromPrimitives(
                'list orders id 1',
                'group id 1',
                'user id 1',
                'list orders name 1',
                'list orders description 1',
                $datetime
            ),
            'list orders id 2' => ListOrders::fromPrimitives(
                'list orders id 2',
                'group id 2',
                'user id 2',
                'list orders name 2',
                'list orders description 2',
                $datetime
            ),
            'list orders id 3' => ListOrders::fromPrimitives(
                'list orders id 3',
                'group id 3',
                'user id 3',
                'list orders name 3',
                'list orders description 3',
                $datetime
            ),
        ];
    }

    private function getListOrdersId(): array
    {
        return [
            ValueObjectFactory::createIdentifier('list orders id 1'),
            ValueObjectFactory::createIdentifier('list orders id 2'),
            ValueObjectFactory::createIdentifier('list orders id 3'),
        ];
    }

    private function assertListOrderDataIsOk(ListOrders $listOrderDataExpected, array $listOrderDataActual): void
    {
        $this->assertArrayHasKey('id', $listOrderDataActual);
        $this->assertArrayHasKey('user_id', $listOrderDataActual);
        $this->assertArrayHasKey('group_id', $listOrderDataActual);
        $this->assertArrayHasKey('name', $listOrderDataActual);
        $this->assertArrayHasKey('description', $listOrderDataActual);
        $this->assertArrayHasKey('date_to_buy', $listOrderDataActual);
        $this->assertArrayHasKey('created_on', $listOrderDataActual);

        $this->assertEquals($listOrderDataExpected->getId()->getValue(), $listOrderDataActual['id']);
        $this->assertEquals($listOrderDataExpected->getUserId()->getValue(), $listOrderDataActual['user_id']);
        $this->assertEquals($listOrderDataExpected->getGroupId()->getValue(), $listOrderDataActual['group_id']);
        $this->assertEquals($listOrderDataExpected->getName()->getValue(), $listOrderDataActual['name']);
        $this->assertEquals($listOrderDataExpected->getDescription()->getValue(), $listOrderDataActual['description']);
        $this->assertEquals($listOrderDataExpected->getDateToBuy()->getValue()->format('Y-m-d H:i:s'), $listOrderDataActual['date_to_buy']);
        $this->assertEquals($listOrderDataExpected->getCreatedOn()->format('Y-m-d H:i:s'), $listOrderDataActual['created_on']);
    }

    /** @test */
    public function itShouldGetListOrdersDataListOrdersId(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersGetDataDto(
            $this->getListOrdersId(),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createNameWithSpaces(null)
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listOrdersId, $input->groupId)
            ->willReturn($this->paginator);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByNameStarsWithOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($listOrders));

        $return = $this->object->__invoke($input);

        $this->assertCount(3, $return);

        foreach ($return as $listOrdersData) {
            $this->assertListOrderDataIsOk($listOrders[$listOrdersData['id']], $listOrdersData);
        }
    }

    /** @test */
    public function itShouldGetListOrdersDataListOrdersNameStartsWith(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersGetDataDto(
            [],
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createNameWithSpaces('list')
        );

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByIdOrFail');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByNameStarsWithOrFail')
            ->with($input->listOrdersNameStartsWith, $input->groupId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($listOrders));

        $return = $this->object->__invoke($input);

        $this->assertCount(3, $return);

        foreach ($return as $listOrdersData) {
            $this->assertListOrderDataIsOk($listOrders[$listOrdersData['id']], $listOrdersData);
        }
    }

    /** @test */
    public function itShouldFailGettingListOrdersDataListOrdersIdNotFound(): void
    {
        $input = new ListOrdersGetDataDto(
            $this->getListOrdersId(),
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createNameWithSpaces(null)
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with($input->listOrdersId, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByNameStarsWithOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGettingListOrdersDataListOrdersNameStartsWithNotFound(): void
    {
        $input = new ListOrdersGetDataDto(
            [],
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createNameWithSpaces('list')
        );

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByIdOrFail');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByNameStarsWithOrFail')
            ->with($input->listOrdersNameStartsWith, $input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGettingListOrdersDataListOrdersIdAndNameStartsWithAreEmpty(): void
    {
        $input = new ListOrdersGetDataDto(
            [],
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createNameWithSpaces(null)
        );

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByIdOrFail');

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByNameStarsWithOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(LogicException::class);
        $this->object->__invoke($input);
    }
}
