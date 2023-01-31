<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Adapter\Database\Orm\Doctrine\Repository\UserGroupRepository;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Test\Unit\DataBaseTestCase;

class UserGroupRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private UserGroupRepository $object;
    private GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(UserGroup::class);
        $this->groupRepository = $this->entityManager->getRepository(Group::class);
    }

    /**
     * @return string[]
     */
    private function getGroupUserIds(): array
    {
        return [
           self::GROUP_USER_ADMIN_ID,
           '0b13e52d-b058-32fb-8507-10dec634a07c',
           '896c6153-794e-3e94-b62d-95997c8b60ad',
           'f425bf79-5a19-31d4-ab56-ed4ca30a7b1a',
           '0b17ca3e-490b-3ddb-aa78-35b4ce668dc0',
           'f1eb9ed5-ccb1-33f4-bb05-19b8b0bea672',
           '1befdbe2-9c14-42f0-850f-63e061e33b8f',
           '2606508b-4516-45d6-93a6-c7cb416b7f3f',
           '6df60afd-f7c3-4c2c-b920-e265f266c560',
        ];
    }

    /** @test */
    public function itShouldFindUsersOfTheGroup(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        $return = $this->object->findGroupUsersOrFail(ValueObjectFactory::createIdentifier(self::GROUP_ID));

        foreach ($return as $userGroup) {
            $this->assertEquals(self::GROUP_ID, $userGroup->getGroupId()->getValue());
            $this->assertContains($userGroup->getUserId()->getValue(), $groupUsersId);
        }
    }

    /** @test */
    public function itShouldFailFindingUsersGroupNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersOrFail(ValueObjectFactory::createIdentifier('not a valid id'));
    }

    /** @test */
    public function itShouldFindUsersWithRolAdminOfTheGroup(): void
    {
        $return = $this->object->findGroupUsersByRol(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            GROUP_ROLES::ADMIN
        );

        $this->assertEquals(self::GROUP_USER_ADMIN_ID, $return[0]->getUserId()->getValue());
    }

    /** @test */
    public function itShouldFindingUsersWithRolUserOfTheGroup(): void
    {
        $expectUsersGroupIds = $this->getGroupUserIds();
        $return = $this->object->findGroupUsersByRol(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            GROUP_ROLES::USER
        );

        $this->assertCount(count($expectUsersGroupIds) - 1, $return);

        foreach ($return as $userGroup) {
            $this->assertContains($userGroup->getUserId()->getValue(), $expectUsersGroupIds);
        }
    }

    /** @test */
    public function itShouldFailFindingGroupUsersWithRolAdmins(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersByRol(
            ValueObjectFactory::createIdentifier('not a valid id'),
            GROUP_ROLES::ADMIN
        );
    }

    /** @test */
    public function itShouldSaveTheUsersGroup(): void
    {
        $group = $this->groupRepository->findBy(['id' => ValueObjectFactory::createIdentifier(self::GROUP_ID)]);
        /** @var Group $group */
        $group = $group[0];
        $expectedUsersId = [
            $this->object->generateId(),
            $this->object->generateId(),
            $this->object->generateId(),
        ];
        $usersGroup = [
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[0], [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
        ];
        $group->setUsers($usersGroup);

        $this->object->save($usersGroup);

        /** @var UserGroup[] $usersGroup */
        $usersGroup = $this->object->findBy(['groupId' => $group->getId()]);

        $countNumUsersSaved = 0;
        foreach ($usersGroup as $userGroup) {
            if (!in_array($userGroup->getUserId()->getValue(), $expectedUsersId)) {
                continue;
            }

            $this->assertEquals($group->getId(), $userGroup->getGroupId());
            $this->assertContains($userGroup->getUserId()->getValue(), $expectedUsersId);
            $this->assertTrue($userGroup->getRoles()->has(ValueObjectFactory::createRol(GROUP_ROLES::USER)));
            ++$countNumUsersSaved;
        }

        $this->assertEquals(3, $countNumUsersSaved);
    }

    /** @test */
    // public function itShouldFailGroupAlreadyExists(): void
    // {
    //     $group = group::fromPrimitives(self::GROUP_ID, 'GroupName', GROUP_TYPE::GROUP, 'description');
    //     $this->
    //     $expectedUsersId = [
    //         $this->object->generateId(),
    //         $this->object->generateId(),
    //         $this->object->generateId(),
    //     ];
    //     $usersGroup = [
    //         UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[0], [GROUP_ROLES::USER], $group),
    //         UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
    //         UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
    //     ];
    //     $group->setUsers($usersGroup);

    //     $this->expectException(DBConnectionException::class);
    //     $this->object->save($usersGroup);
    // }

    /** @test */
    public function itShouldFailDatabaseError(): void
    {
        $group = group::fromPrimitives(self::GROUP_ID, 'GroupName', GROUP_TYPE::GROUP, 'description');
        $expectedUsersId = [
            $this->object->generateId(),
            $this->object->generateId(),
            $this->object->generateId(),
        ];
        $usersGroup = [
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[0], [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, $expectedUsersId[1], [GROUP_ROLES::USER], $group),
        ];
        $group->setUsers($usersGroup);

        $this->expectException(DBConnectionException::class);
        $this->object->save($usersGroup);
    }

    /** @test */
    public function itShouldFindTheNumberOfUsersInTheGroup(): void
    {
        $groupUsers = $this->object->findBy(['groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID)]);
        $return = $this->object->findGroupUsersNumberOrFail(ValueObjectFactory::createIdentifier(self::GROUP_ID));

        $this->assertEquals(count($groupUsers), $return);
    }

    /** @test */
    public function itShouldFailGroupDoesNotExists(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersNumberOrFail(ValueObjectFactory::createIdentifier('invalid group'));
    }
}
