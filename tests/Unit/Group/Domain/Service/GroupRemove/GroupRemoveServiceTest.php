<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\Exception\GroupRemovePermissionsException;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveServiceTest extends TestCase
{
    private const string GROUP_ID = 'id group 1';
    private const string GROUP_2_ID = 'id group 2';
    private const string PATH_GROUP_IMAGES = 'path/to/group/images';

    private GroupRemoveService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private MockObject|EntityImageRemoveService $entityImageRemoveService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->entityImageRemoveService = $this->createMock(EntityImageRemoveService::class);
        $this->object = new GroupRemoveService(
            $this->groupRepository,
            $this->entityImageRemoveService,
            self::PATH_GROUP_IMAGES
        );
    }

    /**
     * @return Group[]
     */
    private function getGroupsData(): array
    {
        return [
            Group::fromPrimitives(self::GROUP_ID, 'group', GROUP_TYPE::GROUP, 'description group 1', null),
            Group::fromPrimitives(self::GROUP_2_ID, 'group 2', GROUP_TYPE::GROUP, 'description group 2', null),
        ];
    }

    /**
     * @param Group[] $groups
     */
    private function entityImageRemoveServiceConsecutiveCalls(Group $group, array $groups): bool
    {
        static $callCounter = 0;

        match (++$callCounter) {
            1 => $this->assertEquals($groups[0], $group),
            2 => [
                $this->assertEquals($groups[1], $group),
                $callCounter = 0,
            ]
        };

        return true;
    }

    /** @test */
    public function itShouldRemoveTheGroup(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
        ];
        $groups = $this->getGroupsData();
        $input = new GroupRemoveDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->entityImageRemoveService
            ->expects($this->exactly(count($groupsId)))
            ->method('__invoke')
            ->with(
                $this->callback(fn (Group $group) => $this->entityImageRemoveServiceConsecutiveCalls($group, $groups)),
                ValueObjectFactory::createPath(self::PATH_GROUP_IMAGES)
            );

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groups);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailTheGroupCanNotRemoveGroupImage(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
        ];
        $groups = $this->getGroupsData();
        $groups[0]->setImage(ValueObjectFactory::createPath('image-1.png'));
        $groups[1]->setImage(ValueObjectFactory::createPath('image-2.png'));
        $input = new GroupRemoveDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->entityImageRemoveService
            ->expects($this->exactly(count($groups)))
            ->method('__invoke')
            ->with(
                $this->callback(fn (Group $group) => $this->entityImageRemoveServiceConsecutiveCalls($group, $groups)),
                ValueObjectFactory::createPath(self::PATH_GROUP_IMAGES)
            )
            ->willThrowException(new DomainInternalErrorException());

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groups);

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
        ];
        $input = new GroupRemoveDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->entityImageRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->groupRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGroupTypeIsUser(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
        ];
        $groups = $this->getGroupsData();
        $groups[0]->setType(ValueObjectFactory::createGroupType(GROUP_TYPE::USER));
        $input = new GroupRemoveDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->entityImageRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->groupRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(GroupRemovePermissionsException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailConnectionError(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
        ];
        $groups = $this->getGroupsData();
        $input = new GroupRemoveDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($groups);

        $this->entityImageRemoveService
            ->expects($this->exactly(count($groups)))
            ->method('__invoke')
            ->with(
                $this->callback(fn (Group $group) => $this->entityImageRemoveServiceConsecutiveCalls($group, $groups)),
                ValueObjectFactory::createPath(self::PATH_GROUP_IMAGES)
            );

        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($groups)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
