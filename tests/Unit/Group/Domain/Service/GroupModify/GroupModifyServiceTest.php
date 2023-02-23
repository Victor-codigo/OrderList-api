<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;
use Group\Domain\Service\GroupModify\GroupModifyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupModifyServiceTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private GroupModifyService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupModifyService($this->groupRepository);
    }

    private function getGroup(): Group
    {
        return Group::fromPrimitives(self::GROUP_ID, 'groupName', GROUP_TYPE::GROUP, 'group description');
    }

    private function getGroupModified(Group $group): Group
    {
        return (clone $group)
            ->setName(ValueObjectFactory::createName('groupNameModified'))
            ->setDescription(ValueObjectFactory::createDescription('group description modified'));
    }

    /** @test */
    public function itShouldModifyTheGroup(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription()
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription()
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willThrowException(new DBNotFoundException());

        $this->groupRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailConnectionOnSave(): void
    {
        $group = $this->getGroup();
        $groupModified = $this->getGroupModified($group);
        $input = new GroupModifyDto(
            $group->getId(),
            $groupModified->getName(),
            $groupModified->getDescription()
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with([$groupModified->getId()])
            ->willReturn([$group]);

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($groupModified)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
