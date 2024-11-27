<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreate\Dto\ListOrdersCreateDto;
use ListOrders\Domain\Service\ListOrdersCreate\Exception\ListOrdersCreateNameAlreadyExistsInGroupException;
use ListOrders\Domain\Service\ListOrdersCreate\ListOrdersCreateService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersCreateServiceTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersCreateService $object;
    private MockObject&ListOrdersRepositoryInterface $listOrdersRepository;
    /**
     * @var MockObject&PaginatorInterface<int, ListOrders>
     */
    private MockObject&PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ListOrdersCreateService($this->listOrdersRepository);
    }

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            'e34083b7-84ee-4b28-85ac-93ff9a629c31',
            self::GROUP_ID,
            self::USER_ID,
            'listOrders name',
            'listOrders description',
            new \DateTime()
        );
    }

    /**
     * @return ListOrders[]
     */
    private function getGroupListOrders(): array
    {
        return [
            ListOrders::fromPrimitives(
                '51a3d66a-389f-4313-a82e-08b1ef176c13',
                self::GROUP_ID,
                self::USER_ID,
                'listOrders name 1',
                'listOrders description 1',
                new \DateTime()
            ),
            ListOrders::fromPrimitives(
                'd4df2cf4-db22-4de2-a86c-d3547254d45b',
                self::GROUP_ID,
                self::USER_ID,
                'listOrders name 2',
                'listOrders description 2',
                new \DateTime()
            ),
            ListOrders::fromPrimitives(
                'ba675342-3144-49c1-bca5-0ac7732b9c06',
                self::GROUP_ID,
                self::USER_ID,
                'listOrders name 3',
                'listOrders description 3',
                new \DateTime()
            ),
        ];
    }

    /**
     * @param ListOrders[] $listsOrdersExpect
     * @param ListOrders[] $listsOrdersActual
     */
    private function assertListOrderIsOk(array $listsOrdersExpect, array $listsOrdersActual): void
    {
        foreach ($listsOrdersExpect as $key => $listOrdersExpect) {
            $this->assertInstanceOf(Identifier::class, $listsOrdersActual[$key]->getId());
            $this->assertEquals($listOrdersExpect->getUserId(), $listsOrdersActual[$key]->getUserId());
            $this->assertEquals($listOrdersExpect->getName(), $listsOrdersActual[$key]->getName());
            $this->assertEquals($listOrdersExpect->getDescription(), $listsOrdersActual[$key]->getDescription());
            $this->assertEquals($listOrdersExpect->getDateToBuy(), $listsOrdersActual[$key]->getDateToBuy());
            $this->assertIsString($listOrdersExpect->getCreatedOn()->format('Y-m-d H:i:s'));
        }
    }

    #[Test]
    public function itShouldCreateAListOrders(): void
    {
        $listOrders = $this->getListOrders();
        $input = new ListOrdersCreateDto(
            $listOrders->getGroupId(),
            $listOrders->getUserId(),
            $listOrders->getName(),
            $listOrders->getDescription(),
            $listOrders->getDateToBuy()
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with([$input->groupId])
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $listsOrdersToSave) use ($listOrders): true {
                $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave);

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertListOrderIsOk([$listOrders], [$return]);
    }

    #[Test]
    public function itShouldFailCreatingAListOrdersNameIsAlreadyInUseInTheGroup(): void
    {
        $listOrders = $this->getListOrders();
        $listOrders->setName(
            ValueObjectFactory::createNameWithSpaces('listOrders name 2')
        );
        $groupListOrders = $this->getGroupListOrders();
        $input = new ListOrdersCreateDto(
            $listOrders->getGroupId(),
            $listOrders->getUserId(),
            $listOrders->getName(),
            $listOrders->getDescription(),
            $listOrders->getDateToBuy()
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with([$input->groupId])
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupListOrders));

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ListOrdersCreateNameAlreadyExistsInGroupException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailCreatingAListOrdersDatabaseErrorUniqueConstraint(): void
    {
        $listOrders = $this->getListOrders();
        $groupListOrders = $this->getGroupListOrders();
        $input = new ListOrdersCreateDto(
            $listOrders->getGroupId(),
            $listOrders->getUserId(),
            $listOrders->getName(),
            $listOrders->getDescription(),
            $listOrders->getDateToBuy()
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with([$input->groupId])
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupListOrders));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                function (array $listsOrdersToSave) use ($listOrders): true {
                    $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave);

                    return true;
                })
            )
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailCreatingAListOrdersDatabaseError(): void
    {
        $listOrders = $this->getListOrders();
        $groupListOrders = $this->getGroupListOrders();
        $input = new ListOrdersCreateDto(
            $listOrders->getGroupId(),
            $listOrders->getUserId(),
            $listOrders->getName(),
            $listOrders->getDescription(),
            $listOrders->getDateToBuy()
        );

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupsListsOrdersOrFail')
            ->with([$input->groupId])
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupListOrders));

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $listsOrdersToSave) use ($listOrders): true {
                $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave);

                return true;
            })
            )
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
