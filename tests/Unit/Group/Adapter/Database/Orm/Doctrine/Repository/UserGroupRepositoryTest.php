<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Group\Adapter\Database\Orm\Doctrine\Repository\UserGroupRepository;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\Attributes\Test;
use Test\Unit\DataBaseTestCase;

class UserGroupRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_2_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_3_ID = 'a5002966-dbf7-4f76-a862-23a04b5ca465';
    private const string GROUP_4_ID = 'e05b2466-9528-4815-ac7f-663c1d89ab55';
    private const string GROUP_5_ID = '78b96ac1-ffcc-458b-8f48-b40c6e65261f';
    private const string GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private UserGroupRepository $object;
    private GroupRepositoryInterface $groupRepository;

    #[\Override]
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

    #[Test]
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

    #[Test]
    public function itShouldFailFindingUsersGroupNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersOrFail(ValueObjectFactory::createIdentifier('not a valid id'));
    }

    #[Test]
    public function itShouldGetGroupsAdmins(): void
    {
        $groupsUsersIdExpected = [
            self::GROUP_ID => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
            self::GROUP_2_ID => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
            self::GROUP_3_ID => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
            self::GROUP_5_ID => [
                'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            ],
        ];

        $return = $this->object->findGroupsUsersOrFail([
            self::GROUP_ID,
            self::GROUP_2_ID,
            self::GROUP_3_ID,
            self::GROUP_5_ID,
        ],
            GROUP_ROLES::ADMIN
        );

        /** @var UserGroup[] $usersGroups */
        $usersGroups = iterator_to_array($return);
        $this->assertCount(count($groupsUsersIdExpected), $usersGroups);

        foreach ($usersGroups as $userGroup) {
            $this->assertArrayHasKey($userGroup->getGroupId()->getValue(), $groupsUsersIdExpected);
            $this->assertContains(
                $userGroup->getUserId()->getValue(),
                $groupsUsersIdExpected[$userGroup->getGroupId()->getValue()]
            );
        }
    }

    #[Test]
    public function itShouldGetGroupsUsers(): void
    {
        $groupsUsersIdExpected = [
            self::GROUP_ID => [
                '1befdbe2-9c14-42f0-850f-63e061e33b8f',
                '08eda546-739f-4ab7-917a-8a9dbee426ef',
                '6df60afd-f7c3-4c2c-b920-e265f266c560',
            ],
            self::GROUP_5_ID => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
        ];

        $return = $this->object->findGroupsUsersOrFail([
            self::GROUP_ID,
            self::GROUP_5_ID,
        ],
            GROUP_ROLES::USER
        );

        /** @var UserGroup[] $usersGroups */
        $usersGroups = iterator_to_array($return);
        $this->assertCount(4, $usersGroups);

        foreach ($usersGroups as $userGroup) {
            $this->assertArrayHasKey($userGroup->getGroupId()->getValue(), $groupsUsersIdExpected);
            $this->assertContains(
                $userGroup->getUserId()->getValue(),
                $groupsUsersIdExpected[$userGroup->getGroupId()->getValue()]
            );
        }
    }

    #[Test]
    public function itShouldFailNoUsersGroupsFound(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsUsersOrFail(['2eca818d-281f-4c7d-908e-b9b57017e1d0'], GROUP_ROLES::ADMIN);
    }

    #[Test]
    public function itShouldFindFirstUserOfAGroupByGroupRoleAdmin(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
        ];

        $return = $this->object->findGroupsFirstUserByRolOrFail($groupsId, GROUP_ROLES::ADMIN);
        $expected = $this->object->findBy([
            'groupId' => $groupsId,
            'userId' => ValueObjectFactory::createIdentifier('2606508b-4516-45d6-93a6-c7cb416b7f3f'),
        ]);

        $this->assertEqualsCanonicalizing($expected, $return);
    }

    #[Test]
    public function itShouldFindFirstUserOfAGroupByGroupRoleUser(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
        ];

        $return = $this->object->findGroupsFirstUserByRolOrFail($groupsId, GROUP_ROLES::USER);
        $expected = $this->object->findBy([
            'groupId' => $groupsId,
            'userId' => [
                ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8f'),
                ValueObjectFactory::createIdentifier('4d59c61e-4f51-3ffe-b4b9-e622436b6fa3'),
            ]]);

        $this->assertEqualsCanonicalizing($expected, $return);
    }

    #[Test]
    public function itShouldFailFindFirstUserOfAGroupByGroupRoleNotFound(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsFirstUserByRolOrFail($groupsId, GROUP_ROLES::USER);
    }

    #[Test]
    public function itShouldFindUsersOfTheGroupByGroupAndUserId(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        $groupUsersIdentifiers = array_map(
            fn (string $userId): Identifier => ValueObjectFactory::createIdentifier($userId),
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

    #[Test]
    public function itShouldFindOnlyEightUsersOfTheGroupByGroupAndUserId(): void
    {
        $groupUsersId = $this->getGroupUserIds();
        // Users that do not exists in data base
        $groupUsersId[] = 'ac2622c7-a38a-4581-980e-a63bec3cc5f0';
        $groupUsersId[] = '9212bf15-915c-4903-824b-6b177e338cde';
        $groupUsersIdentifiers = array_map(
            fn (string $userId): Identifier => ValueObjectFactory::createIdentifier($userId),
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

    #[Test]
    public function itShouldFindNotFindUsersOfTheGroupByGroupAndUserId(): void
    {
        // Users that do not exists in data base
        $groupUsersId = [
            'ac2622c7-a38a-4581-980e-a63bec3cc5f0',
            '9212bf15-915c-4903-824b-6b177e338cde',
        ];
        $groupUsersIdentifiers = array_map(
            fn (string $userId): Identifier => ValueObjectFactory::createIdentifier($userId),
            $groupUsersId
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersByUserIdOrFail(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            $groupUsersIdentifiers
        );
    }

    #[Test]
    public function itShouldFindUsersWithRolAdminOfTheGroup(): void
    {
        $return = $this->object->findGroupUsersByRol(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            GROUP_ROLES::ADMIN
        );

        $this->assertEquals(self::GROUP_USER_ADMIN_ID, $return[0]->getUserId()->getValue());
    }

    #[Test]
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

    #[Test]
    public function itShouldFailFindingGroupUsersWithRolAdmins(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersByRol(
            ValueObjectFactory::createIdentifier('not a valid id'),
            GROUP_ROLES::ADMIN
        );
    }

    #[Test]
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

    #[Test]
    public function itShouldOnlyGroupsIsAdmin(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $groups = array_filter(
            $this->object->findBy(['userId' => $userId]),
            fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has(Rol::fromString(GROUP_ROLES::ADMIN->value))
        );

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::ADMIN);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    #[Test]
    public function itShouldOnlyGroupsIsUser(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);
        $groups = array_filter(
            $this->object->findBy(['userId' => $userId]),
            fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has(Rol::fromString(GROUP_ROLES::USER->value))
        );

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::USER);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    #[Test]
    public function itShouldOnlyGroupsTypeGroup(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);

        $groups = $this->object->createQueryBuilder('u')
            ->leftJoin(Group::class, 'g', Join::WITH, 'u.groupId = g.id')
            ->where('g.type = :type')
            ->andWhere('u.userId = :userId')
            ->setParameters(new ArrayCollection([
                new Parameter('type', GROUP_TYPE::GROUP),
                new Parameter('userId', $userId),
            ]))
            ->getQuery()
            ->getResult();

        $return = $this->object->findUserGroupsById($userId, null, GROUP_TYPE::GROUP);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    #[Test]
    public function itShouldOnlyGroupsTypeGroupAndIsUser(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID);

        $groups = $this->object->createQueryBuilder('u')
            ->leftJoin(Group::class, 'g', Join::WITH, 'u.groupId = g.id')
            ->where('u.userId = :userId')
            ->andWhere('g.type = :type')
            ->andWhere('JSON_CONTAINS(u.roles, :groupRoles) = 1')
            ->setParameters(new ArrayCollection([
                new Parameter('userId', $userId),
                new Parameter('type', GROUP_TYPE::GROUP->value),
                new Parameter('groupRoles', '"'.GROUP_ROLES::ADMIN->value.'"'),
            ]))
            ->getQuery()
            ->getResult();

        $return = $this->object->findUserGroupsById($userId, GROUP_ROLES::ADMIN, GROUP_TYPE::GROUP);

        $this->assertCount(count($groups), $return);

        foreach ($groups as $group) {
            $this->assertContains($group, $return);
        }
    }

    #[Test]
    public function itShouldFailNotGroupsFound(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID.'-');

        $this->expectException(DBNotFoundException::class);
        $this->object->findUserGroupsById($userId);
    }

    #[Test]
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

    #[Test]
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
                ValueObjectFactory::createIdentifier(self::GROUP_5_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
    }

    #[Test]
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

    #[Test]
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
                ValueObjectFactory::createIdentifier(self::GROUP_5_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
    }

    #[Test]
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

    #[Test]
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
                ValueObjectFactory::createIdentifier(self::GROUP_5_ID),
            ],
            'userId' => ValueObjectFactory::createIdentifier(self::GROUP_USER_ADMIN_ID),
        ]);

        $this->assertEqualsCanonicalizing($expectedGroups, iterator_to_array($return));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function itShouldFindTheNumberOfUsersInTheGroup(): void
    {
        $groupUsers = $this->object->findBy(['groupId' => ValueObjectFactory::createIdentifier(self::GROUP_ID)]);
        $return = $this->object->findGroupUsersNumberOrFail(ValueObjectFactory::createIdentifier(self::GROUP_ID));

        $this->assertEquals(count($groupUsers), $return);
    }

    #[Test]
    public function itShouldFailGroupDoesNotExists(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupUsersNumberOrFail(ValueObjectFactory::createIdentifier('invalid group'));
    }

    #[Test]
    public function itShouldFindGroupsNumberOfUsers(): void
    {
        $groupsId = [
            self::GROUP_ID => ValueObjectFactory::createIdentifier(self::GROUP_ID),
            self::GROUP_2_ID => ValueObjectFactory::createIdentifier(self::GROUP_2_ID),
            self::GROUP_3_ID => ValueObjectFactory::createIdentifier(self::GROUP_3_ID),
        ];
        $return = $this->object->findGroupsUsersNumberOrFail($groupsId);

        $groupIdUsers = $this->object->findBy([
            'groupId' => $groupsId[self::GROUP_ID],
        ]);
        $group2IdUsers = $this->object->findBy([
            'groupId' => $groupsId[self::GROUP_2_ID],
        ]);
        $group3IdUsers = $this->object->findBy([
            'groupId' => $groupsId[self::GROUP_3_ID],
        ]);

        $expected = [
            [
                'groupId' => $groupsId[self::GROUP_2_ID],
                'groupUsers' => count($group2IdUsers),
            ],
            [
                'groupId' => $groupsId[self::GROUP_3_ID],
                'groupUsers' => count($group3IdUsers),
            ],
            [
                'groupId' => $groupsId[self::GROUP_ID],
                'groupUsers' => count($groupIdUsers),
            ],
        ];

        $this->assertEquals($expected, iterator_to_array($return));
    }

    #[Test]
    public function itShouldFindGroupsNumberOfUsersNotFoundException(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('9b54e906-3a2f-406b-92c8-51b60143ec20'),
        ];
        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsUsersNumberOrFail($groupsId);
    }

    #[Test]
    public function itShouldRemoveUsersGroup(): void
    {
        $usersId = array_map(
            fn (string $userId): Identifier => ValueObjectFactory::createIdentifier($userId),
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
