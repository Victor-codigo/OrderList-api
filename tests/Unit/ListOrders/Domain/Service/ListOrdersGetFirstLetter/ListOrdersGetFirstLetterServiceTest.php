<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Domain\Service\ListOrdersGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterDto;
use ListOrders\Domain\Service\ListOrdersGetFirstLetter\ListOrdersGetFirstLetterService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersGetFirstLetterServiceTest extends TestCase
{
    private ListOrdersGetFirstLetterService $object;
    private MockObject&ListOrdersRepositoryInterface $listOrdersRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->object = new ListOrdersGetFirstLetterService($this->listOrdersRepository);
    }

    #[Test]
    public function itShouldGetListOrdersFirstLetterOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $expected = ['a', 'b', 'c'];
        $input = new ListOrdersGetFirstLetterDto($groupId);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupListOrdersFirstLetterOrFail')
            ->with($input->groupId)
            ->willReturn($expected);

        $return = $this->object->__invoke($input);

        $this->assertSame($expected, $return);
    }

    #[Test]
    public function itShouldFailGetListOrdersFirstLetterOfAGroupNoListOrders(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $input = new ListOrdersGetFirstLetterDto($groupId);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findGroupListOrdersFirstLetterOrFail')
            ->with($input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
