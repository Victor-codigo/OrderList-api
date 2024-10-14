<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Group\Adapter\Database\Orm\Doctrine\Repository\GroupRepository;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class GroupRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @var GroupRepository|EntityRepository<Group>
     */
    private GroupRepository|EntityRepository $object;
    private MockObject|ConnectionException $connectionException;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Group::class);
        $this->connectionException = $this->createMock(ConnectionException::class);
    }

    private function getNewGroup(): Group
    {
        return Group::fromPrimitives(
            'ddb1a26e-ed57-4e83-bc5c-0ee6f6f5c8f8',
            'New group',
            GROUP_TYPE::USER,
            'This is a new group',
            null
        );
    }

    private function getExistsGroup(): Group
    {
        return Group::fromPrimitives(
            'fdb242b4-bac8-4463-88d0-0941bb0beee0',
            'Group One',
            GROUP_TYPE::GROUP,
            'This is a group of users',
            'image.png'
        );
    }

    private function getOtherExistsGroup(): Group
    {
        return Group::fromPrimitives(
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Group Other',
            GROUP_TYPE::GROUP,
            'This is a group of other users',
            'otherImage.png'
        );
    }

    /**
     * @param GROUP_ROLES[] $roles
     */
    private function getNewUserGroup(Group $group, array $roles): UserGroup
    {
        return UserGroup::fromPrimitives(
            $group->getId()->getValue(),
            '141a0e83-fa01-453f-8177-63c87dba56fd',
            $roles,
            $group
        );
    }

    #[Test]
    public function itShouldSaveTheGroupInDatabase(): void
    {
        $groupNew = $this->getNewGroup();
        $userGroupNew = $this->getNewUserGroup($groupNew, [GROUP_ROLES::ADMIN]);
        $groupNew->addUserGroup($userGroupNew);
        $this->object->save($groupNew);

        /** @var Group $groupSaved */
        $groupSaved = $this->object->findOneBy(['id' => $groupNew->getId()]);

        $this->assertSame($groupNew, $groupSaved);
        $this->assertCount(1, $groupSaved->getUsers());
        $this->assertEquals($userGroupNew, $groupSaved->getUsers()->get(0));
    }

    #[Test]
    public function itShouldFailIdAlreadyExists(): void
    {
        $this->expectException(DBUniqueConstraintException::class);

        $this->object->save($this->getExistsGroup());
    }

    #[Test]
    public function itShouldFailDataBaseError(): void
    {
        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->save($this->getNewGroup());
    }

    #[Test]
    public function itShouldRemoveTheGroup(): void
    {
        $groups = [
            $this->getOtherExistsGroup(),
            $this->getExistsGroup(),
        ];
        $groupsToRemove = $this->object->findBy(['id' => [
            $groups[0]->getId(),
            $groups[1]->getId(),
        ],
        ]);

        $this->object->remove($groupsToRemove);

        $groupRemoved = $this->object->findBy(['id' => [
            $groups[0]->getId(),
            $groups[1]->getId(),
        ],
        ]);

        $this->assertEmpty($groupRemoved);
    }

    #[Test]
    public function itShouldFailRemovingTheGroupErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $group = $this->getNewGroup();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$group]);
    }

    #[Test]
    public function itShouldFindAllGroupsById(): void
    {
        $groupId = [
            $this->getExistsGroup()->getId(),
            $this->getOtherExistsGroup()->getId(),
        ];
        $return = $this->object->findGroupsByIdOrFail($groupId);

        $this->assertCount(2, $return);

        foreach ($return as $group) {
            $this->assertContainsEquals($group->getId(), $groupId);
        }
    }

    #[Test]
    public function itShouldFailNoIdGroup(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->object->findGroupsByIdOrFail([ValueObjectFactory::createIdentifier('0b13e52d-b058-32fb-8507-10dec634a07A')]);
    }

    #[Test]
    public function itShouldFindAGroupByName(): void
    {
        $groupName = ValueObjectFactory::createNameWithSpaces('GroupOne');
        $return = $this->object->findGroupByNameOrFail($groupName);
        $returnExpected = $this->object->findBy(['name' => $groupName]);

        $this->assertEquals($returnExpected[0], $return);
    }

    #[Test]
    public function itShouldFailFindingAGroupByNameNotFound(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->object->findGroupsByIdOrFail([ValueObjectFactory::createNameWithSpaces('namNotFound')]);
    }
}
