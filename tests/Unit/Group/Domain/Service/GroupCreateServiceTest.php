<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use DateTime;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;
use Group\Domain\Service\GroupCreate\GroupCreateService;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class GroupCreateServiceTest extends DataBaseTestCase
{
    private GroupCreateService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupCreateService($this->groupRepository);
    }

    private function createGroupCreateDto(): GroupCreateDto
    {
        return new GroupCreateDto(
            ValueObjectFactory::createIdentifier('87c635dd-1861-430e-bbf8-9f21ac4b1b86'),
            ValueObjectFactory::createName('GroupName'),
            ValueObjectFactory::createDescription('This is a description of the group'),
        );
    }

    private function assertGroupIsCreated(Group $group, GroupCreateDto $groupCreateDto): void
    {
        $this->assertInstanceOf(Identifier::class, $group->getId());
        $this->assertSame($groupCreateDto->name, $group->getName());
        $this->assertSame($groupCreateDto->description, $group->getDescription());
        $this->assertEquals(ValueObjectFactory::createGroupType(GROUP_TYPE::USER), $group->getType());
        $this->assertInstanceOf(DateTime::class, $group->getCreatedOn());

        $userGroupCollection = $group->getUsers();
        $this->assertCount(1, $userGroupCollection);
        $this->assertContainsOnlyInstancesOf(UserGroup::class, $userGroupCollection);
        /** @var UserGroup $userGroup */
        $userGroup = $userGroupCollection->get(0);
        $this->assertEquals($groupCreateDto->userCreatorId, $userGroup->getUserId());
        $this->assertEquals($group->getId(), $userGroup->getGroupId());
        $this->assertEquals(
            ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::ADMIN)]),
            $userGroup->getRoles()
        );
    }

    /** @test */
    public function itShouldCreateTheGroup(): void
    {
        $groupCreateDto = $this->createGroupCreateDto();

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto) || true));

        $return = $this->object->__invoke($groupCreateDto);

        $this->assertInstanceOf(Group::class, $return);
        $this->assertSame($groupCreateDto->name, $return->getName());
        $this->assertSame($groupCreateDto->description, $return->getDescription());
        $this->assertEquals(ValueObjectFactory::createGroupType(GROUP_TYPE::USER), $return->getType());
    }

    /** @test */
    public function itShouldFailIdIsAlreadyRegistered(): void
    {
        $this->expectException(DBUniqueConstraintException::class);

        $groupCreateDto = $this->createGroupCreateDto();

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto) || true))
            ->willThrowException(new DBUniqueConstraintException());

        $this->object->__invoke($groupCreateDto);
    }

    /** @test */
    public function itShouldFailDatabaseConnectionException(): void
    {
        $this->expectException(DBConnectionException::class);

        $groupCreateDto = $this->createGroupCreateDto();

        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Group $group) => $this->assertGroupIsCreated($group, $groupCreateDto) || true))
            ->willThrowException(new DBConnectionException());

        $this->object->__invoke($groupCreateDto);
    }
}
