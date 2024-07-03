<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders;

use Override;
use ArrayIterator;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\ListOrdersRemoveAllGroupsListsOrdersService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveAllGroupsListsOrdersServiceTest extends TestCase
{
    private ListOrdersRemoveAllGroupsListsOrdersService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|PaginatorInterface $listsOrdersToRemovePaginator;
    private MockObject|PaginatorInterface $listsOrdersToChangeUserIdPaginator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->listsOrdersToRemovePaginator = $this->createMock(PaginatorInterface::class);
        $this->listsOrdersToChangeUserIdPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersRemoveAllGroupsListsOrdersService($this->listOrdersRepository);
    }

    /**
     * @return ListOrders[]
     */
    private function getListsOrdersToRemove(): array
    {
        return [
            ListOrders::fromPrimitives(
                'listOrders id 1',
                'group id 1',
                'user id 1',
                'listOrders name 1',
                'listOrders description 1',
                null
            ),
            ListOrders::fromPrimitives(
                'listOrders id 2',
                'group id 1',
                'user id 2',
                'listOrders name 2',
                'listOrders description 2',
                null
            ),
            ListOrders::fromPrimitives(
                'listOrders id 3',
                'group id 1',
                'user id 3',
                'listOrders name 3',
                'listOrders description 3',
                null
            ),
        ];
    }

    /**
     * @return ListOrders[]
     */
    private function getListsOrdersToChangeUserId(): array
    {
        return [
            ListOrders::fromPrimitives(
                'listOrders id 4',
                'group id 4',
                'user id 4',
                'listOrders name 4',
                'listOrders description 4',
                null
            ),
            ListOrders::fromPrimitives(
                'listOrders id 5',
                'group id 5',
                'user id 5',
                'listOrders name 5',
                'listOrders description 5',
                null
            ),
            ListOrders::fromPrimitives(
                'listOrders id 6',
                'group id 6',
                'user id 6',
                'listOrders name 6',
                'listOrders description 6',
                null
            ),
        ];
    }

    /**
     * @param ListOrders[] $listsOrders
     *
     * @return ListOrders[]
     */
    private function getListsOrdersToChangeUserIdAlreadyChanged(array $listsOrders): array
    {
        $listsOrdersToModify = array_map(
            fn (ListOrders $listOrders) => clone $listOrders,
            $listsOrders
        );

        $listsOrdersToModify[0]->setUserId(ValueObjectFactory::createIdentifier('admin id 4'));
        $listsOrdersToModify[1]->setUserId(ValueObjectFactory::createIdentifier('admin id 5'));
        $listsOrdersToModify[2]->setUserId(ValueObjectFactory::createIdentifier('admin id 6'));

        return $listsOrdersToModify;
    }

    /**
     * @return Identifier[]
     */
    private function getListsOrdersId(array $listsOrders): array
    {
        return array_map(
            fn (ListOrders $listOrders) => $listOrders->getId(),
            $listsOrders
        );
    }

    private function getGroupsIdToRemove(): array
    {
        return [
            ValueObjectFactory::createIdentifier('group id 1'),
            ValueObjectFactory::createIdentifier('group id 2'),
            ValueObjectFactory::createIdentifier('group id 3'),
        ];
    }

    private function getGroupsIdToChangeUserId(): array
    {
        return [
            ValueObjectFactory::createIdentifier('group id 4'),
            ValueObjectFactory::createIdentifier('group id 5'),
            ValueObjectFactory::createIdentifier('group id 6'),
        ];
    }

    private function getGroupsIdAndAdminToChangeUserId(): array
    {
        return [[
            'group_id' => ValueObjectFactory::createIdentifier('group id 4'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 4'),
        ], [
            'group_id' => ValueObjectFactory::createIdentifier('group id 5'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 5'),
        ], [
            'group_id' => ValueObjectFactory::createIdentifier('group id 6'),
            'admin' => ValueObjectFactory::createIdentifier('admin id 6'),
        ]];
    }

    /** @test */
    public function itShouldRemoveGroupListsOrdersAndSetListsOrdersUserId(): void
    {
        $listsOrdersToRemove = $this->getListsOrdersToRemove();
        $listsOrdersIdToRemove = $this->getListsOrdersId($listsOrdersToRemove);
        $listsOrdersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $listsOrdersToChangeUserIdExpected = $this->getListsOrdersToChangeUserIdAlreadyChanged($listsOrdersToChangeUserId);
        $listsOrdersIdToChangeUserId = $this->getListsOrdersId($listsOrdersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $listOrdersRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($listOrdersRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($listOrdersRepositoryMatcher, $input, $groupsIdToChangeUserId) {
                match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->listsOrdersToRemovePaginator,
                $this->listsOrdersToChangeUserIdPaginator
            );

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listsOrdersToRemove);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($listsOrdersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEquals($listsOrdersIdToRemove, $return->listsOrdersIdRemoved);
        $this->assertEquals($listsOrdersIdToChangeUserId, $return->listsOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeListsOrdersUsersId(): void
    {
        $listsOrdersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $listsOrdersIdToChangeUserId = $this->getListsOrdersId($listsOrdersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $listsOrdersToChangeUserIdExpected = $this->getListsOrdersToChangeUserIdAlreadyChanged($listsOrdersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            [],
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($groupsIdToChangeUserId)
            ->willReturn($this->listsOrdersToChangeUserIdPaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($listsOrdersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->listsOrdersIdRemoved);
        $this->assertEquals($listsOrdersIdToChangeUserId, $return->listsOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyChangeListsOrdersUsersIdGroupsIdToRemoveNotFound(): void
    {
        $listsOrdersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $listsOrdersIdToChangeUserId = $this->getListsOrdersId($listsOrdersToChangeUserId);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $listsOrdersToChangeUserIdExpected = $this->getListsOrdersToChangeUserIdAlreadyChanged($listsOrdersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $listOrdersRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($listOrdersRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($listOrdersRepositoryMatcher, $input, $groupsIdToChangeUserId) {
                match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(fn() => match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                1 => throw new DBNotFoundException(),
                2 => $this->listsOrdersToChangeUserIdPaginator
            });

        $this->listsOrdersToRemovePaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('remove');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($listsOrdersToChangeUserIdExpected);

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return->listsOrdersIdRemoved);
        $this->assertEquals($listsOrdersIdToChangeUserId, $return->listsOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailRemoveListsOrdersError(): void
    {
        $listsOrdersToRemove = $this->getListsOrdersToRemove();
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($input->groupsIdToRemoveListsOrders)
            ->willReturn($this->listsOrdersToRemovePaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listsOrdersToRemove)
            ->willThrowException(new DBConnectionException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldOnlyRemoveGroupListsOrders(): void
    {
        $listsOrdersToRemove = $this->getListsOrdersToRemove();
        $listsOrdersIdToRemove = $this->getListsOrdersId($listsOrdersToRemove);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            [],
            null
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with($input->groupsIdToRemoveListsOrders)
            ->willReturn($this->listsOrdersToRemovePaginator);

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listsOrdersToRemove);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($listsOrdersIdToRemove, $return->listsOrdersIdRemoved);
        $this->assertEmpty($return->listsOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldOnlyRemoveListsOrdersFromGroupsIdToRemoveListsOrdersToChangeUserIdGroupsIdNotFound(): void
    {
        $listsOrdersToRemove = $this->getListsOrdersToRemove();
        $listsOrdersIdToRemove = $this->getListsOrdersId($listsOrdersToRemove);
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getListsOrdersToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $listOrdersRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($listOrdersRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($listOrdersRepositoryMatcher, $input, $groupsIdToChangeUserId) {
                match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnCallback(fn() => match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                1 => $this->listsOrdersToRemovePaginator,
                2 => throw new DBNotFoundException()
            });

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listsOrdersToRemove);

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->object->__invoke($input);

        $this->assertEquals($listsOrdersIdToRemove, $return->listsOrdersIdRemoved);
        $this->assertEmpty($return->listsOrdersIdChangedUserId);
    }

    /** @test */
    public function itShouldFailChangingListsOrdersUserId(): void
    {
        $listsOrdersToRemove = $this->getListsOrdersToRemove();
        $listsOrdersToChangeUserId = $this->getListsOrdersToChangeUserId();
        $groupsIdToChangeUserId = $this->getGroupsIdToChangeUserId();
        $listsOrdersToChangeUserIdExpected = $this->getListsOrdersToChangeUserIdAlreadyChanged($listsOrdersToChangeUserId);
        $input = new ListOrdersRemoveAllGroupsListsOrdersDto(
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdAndAdminToChangeUserId(),
            ValueObjectFactory::createIdentifier('user id')
        );

        $listOrdersRepositoryMatcher = $this->exactly(2);
        $this->listOrdersRepository
            ->expects($listOrdersRepositoryMatcher)
            ->method('findGroupsListsOrdersOrFail')
            ->with($this->callback(function (array $groupsId) use ($listOrdersRepositoryMatcher, $input, $groupsIdToChangeUserId) {
                match ($listOrdersRepositoryMatcher->getInvocationCount()) {
                    1 => $this->assertEquals($input->groupsIdToRemoveListsOrders, $groupsId),
                    2 => $this->assertEquals($groupsIdToChangeUserId, $groupsId)
                };

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $this->listsOrdersToRemovePaginator,
                $this->listsOrdersToChangeUserIdPaginator
            );

        $this->listsOrdersToRemovePaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToRemove));

        $this->listsOrdersToChangeUserIdPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new ArrayIterator($listsOrdersToChangeUserId));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('remove')
            ->with($listsOrdersToRemove);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($listsOrdersToChangeUserIdExpected)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
