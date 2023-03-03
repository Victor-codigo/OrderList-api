<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveServiceTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private GroupRemoveService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupRemoveService($this->groupRepository);
    }

    private function getGroup(): Group
    {
        return Group::fromPrimitives(self::GROUP_ID, 'group', GROUP_TYPE::GROUP, 'description', null);
    }

    /** @test */
    public function itShouldRemoveTheGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $group = $this->getGroup();
        $input = new GroupRemoveDto($groupId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupId])
            ->willReturn([$group]);

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($group);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $input = new GroupRemoveDto($groupId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupId])
            ->willThrowException(new DBNotFoundException());

        $this->groupRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailConnectionError(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $group = $this->getGroup();
        $input = new GroupRemoveDto($groupId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupId])
            ->willReturn([$group]);

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($group)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
