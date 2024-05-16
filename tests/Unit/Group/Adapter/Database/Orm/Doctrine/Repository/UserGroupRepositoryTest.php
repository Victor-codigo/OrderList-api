<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\ORM\Query\Expr\Join;
use Group\Adapter\Database\Orm\Doctrine\Repository\UserGroupRepository;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Test\Unit\DataBaseTestCase;

class UserGroupRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_2_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_3_ID = 'a5002966-dbf7-4f76-a862-23a04b5ca465';
    private const GROUP_4_ID = 'e05b2466-9528-4815-ac7f-663c1d89ab55';
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
            '1befdbe2-9c14-42f0-850f-63e061e33b8f',
            '08eda546-739f-4ab7-917a-8a9dbee426ef',
            '6df60afd-f7c3-4c2c-b920-e265f266c560',
        ];
    }

    /** @test */
    public function itShouldFindUsersOfTheGroup(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        $return = $this->object->findGroupUsersOrFail(ValueObjectFactory::createIdentifier(self::GROUP_ID));

        $this->assertCount(4, $return);

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
    public function itShouldFindUsersOfTheGroupByGroupAndUserId(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        $groupUsersIdentifiers = array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $groupUsersId
        );

        $return = $this->object->findGroupUsersByUserIdOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $groupUsersIdentifiers
        );

        $this->assertCount(count($groupUsersId), $return);

        foreach ($return as $userGroup) {
            $this->assertEquals(self::GROUP_ID, $userGroup->getGroupId()->getValue());
            $this->assertContains($userGroup->getUserId()->getValue(), $groupUsersId);
        }
    }

    /** @test */
    public function itShouldFindOnlyEightUsersOfTheGroupByGroupAndUserId(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        // Users that do not exists in data base
        $groupUsersId[] = 'ac2622c7-a38a-4581-980e-a63bec3cc5f0';
        $groupUsersId[] = '9212bf15-915c-4903-824b-6b177e338cde';
        $groupUsersIdentifiers = array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $groupUsersId
        );

        $return = $this->object->findGroupUsersByUserIdOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $groupUsersIdentifiers
        );

        $this->assertCount(count($groupUsersId) - 2, $return);

        foreach ($return as $userGroup) {
            $this->assertEquals(self::GROUP_ID, $userGroup->getGroupId()->getValue());
            $this->assertContains($userGroup->getUserId()->getValue(), $groupUsersId);
        }
    }

    /** @test */
    public function itShouldFindNotFindUsersOfTheGroupByGroupAndUserId(): void
    {
        // Users that do not exists in data base
        $groupUsersId = [
            'ac2622c7-a38a-4581-980e-a63bec3cc5f0',
            '9212bf15-915c-4903-824b-6b177e338cde',
        ];
        $groupUsersIdentifiers = array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $groupUsersId
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersByUserIdOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $groupUsersIdentifiers
        );
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
    public function itShouldFindThreeGroups(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $groups = $this->object->findBy(['userId' => $userId]);
        $return = $this->object->findUserGroupsById($userId);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    /** @test */
    public function itShouldOnlyGroupsIsAdmin(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $groups = array_filter(
            $this->object->findBy(['userId' => $userId]),
            fn (UserGroup $userGroup) => $userGroup->getRoles()->has(Rol::fromString(GROUP_ROLES::ADMIN->value))
        );

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::ADMIN);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    /** @test */
    public function itShouldOnlyGroupsIsUser(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $groups = array_filter(
            $this->object->findBy(['userId' => $userId]),
            fn (UserGroup $userGroup) => $userGroup->getRoles()->has(Rol::fromString(GROUP_ROLES::USER->value))
        );

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::USER);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    /** @test */
    public function itShouldOnlyGroupsTypeGroup(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);

        $groups = $this->object->createQueryBuilder('u')
            ->leftJoin(Group::class, 'g', Join::WITH, 'u.groupId = g.id')
            ->where('g.type = :type')
            ->andWhere('u.userId = :userId')
            ->setParameters([
                'type' => GROUP_TYPE::GROUP,
                'userId' => $userId,
            ])
            ->getQuery()
            ->getResult();

        $return = $this->object->findUserGroupsById($userId, null, GROUP_TYPE::GROUP);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    /** @test */
    public function itShouldOnlyGroupsTypeGroupAndIsUser(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);

        $groups = $this->object->createQueryBuilder('u')
            ->leftJoin(Group::class, 'g', Join::WITH, 'u.groupId = g.id')
            ->where('u.userId = :userId')
            ->andWhere('g.type = :type')
            ->andWhere('JSON_CONTAINS(u.roles, :groupRoles) = 1')
            ->setParameters([
                'userId' => $userId,
                'type' => GROUP_TYPE::GROUP->value,
                'groupRoles' => '"'.GROUP_ROLES::ADMIN->value.'"',
            ])
            ->getQuery()
            ->getResult();

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::ADMIN, GROUP_TYPE::GROUP);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    /** @test */
    public function itShouldFailNotGroupsFound(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID.'-');

        $this->expectException(DBNotFoundException::class);
        $this->object->findUserGroupsById($userId);
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterEqualsAndGroupTypeGroup(): void
    {
        $groupName = 'GroupOne';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, GROUP_TYPE::GROUP, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEquals($expectedGroups, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterStartsWithAndGroupTypeGroup(): void
    {
        $groupName = 'Group';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, GROUP_TYPE::GROUP, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => [
                ValueObjectFactory::createIdentifier(self::GROUP_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_4_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $r = iterator_to_array($return);
        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterEndsWithAndGroupTypeGroup(): void
    {
        $groupName = 'One';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, GROUP_TYPE::GROUP, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEquals($expectedGroups, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterContainsAndGroupTypeGroup(): void
    {
        $groupName = 'oup';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, GROUP_TYPE::GROUP, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => [
                ValueObjectFactory::createIdentifier(self::GROUP_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_4_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterStartsWithAndGroupTypeUser(): void
    {
        $groupName = 'oup';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, GROUP_TYPE::USER, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEquals($expectedGroups, iterator_to_array($return));
    }

    /** @test */
    public function itShouldGetUserGroupsByFilterStartsWithAndGroupTypeNotSet(): void
    {
        $groupName = 'Group';
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $filterText = ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces($groupName)
        );

        $return = $this->object->findUserGroupsByName($userId, $filterText, null, true);
        $expectedGroups = $this->object->findBy([
            'groupId' => [
                ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
                ValueObjectFactory::createIdentifier(self::GROUP_4_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
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
    public function itShouldFailDatabaseError(): void
    {
        $group = Group::fromPrimitives(self::GROUP_ID, 'GroupName', GROUP_TYPE::GROUP, 'description', null);
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

    /** @test */
    public function itShouldRemoveUsersGroup(): void
    {
        $usersId = array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            array_slice($this->getGroupUserIds(), 0, 3)
        );
        $usersGroup = $this->object->findBy([
            'groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'userId' => $usersId,
        ]);

        $this->object->removeUsers($usersGroup);

        $usersGroupAfter = $this->object->findBy([
            'groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'userId' => $usersId,
        ]);

        $this->assertEmpty($usersGroupAfter);
    }
}
