<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreate\Dto\ListOrdersCreateDto;
use ListOrders\Domain\Service\ListOrdersCreate\ListOrdersCreateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersCreateServiceTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersCreateService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
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

    /** @test */
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
            ->method('save')
            ->with($this->callback(
                fn (array $listsOrdersToSave) => $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave) || true)
            );

        $return = $this->object->__invoke($input);

        $this->assertListOrderIsOk([$listOrders], [$return]);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersDatabaseErrorUniqueConstraint(): void
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
            ->method('save')
            ->with($this->callback(
                fn (array $listsOrdersToSave) => $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave) || true)
            )
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCreatingAListOrdersDatabaseError(): void
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
            ->method('save')
            ->with($this->callback(
                fn (array $listsOrdersToSave) => $this->assertListOrderIsOk([$listOrders], $listsOrdersToSave) || true)
            )
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
