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
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ListOrdersCreateService $object;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;

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

    private function assertListOrderIsOk(ListOrders $listOrdersExpect, ListOrders $listOrdersActual): void
    {
        $this->assertInstanceOf(Identifier::class, $listOrdersActual->getId());
        $this->assertEquals($listOrdersExpect->getUserId(), $listOrdersActual->getUserId());
        $this->assertEquals($listOrdersExpect->getName(), $listOrdersActual->getName());
        $this->assertEquals($listOrdersExpect->getDescription(), $listOrdersActual->getDescription());
        $this->assertEquals($listOrdersExpect->getDateToBuy(), $listOrdersActual->getDateToBuy());
        $this->assertIsString($listOrdersExpect->getCreatedOn()->format('Y-m-d H:i:s'));
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
                fn (ListOrders $listOrdersToSave) => $this->assertListOrderIsOk($listOrders, $listOrdersToSave) || true)
            );

        $return = $this->object->__invoke($input);

        $this->assertListOrderIsOk($listOrders, $return);
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
                fn (ListOrders $listOrdersToSave) => $this->assertListOrderIsOk($listOrders, $listOrdersToSave) || true)
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
                fn (ListOrders $listOrdersToSave) => $this->assertListOrderIsOk($listOrders, $listOrdersToSave) || true)
            )
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
