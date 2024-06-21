<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class UserGroupRepository extends RepositoryBase implements UserGroupRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, UserGroup::class, $paginator);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersOrFail(Identifier $groupId): PaginatorInterface
    {
        $userGroupTable = UserGroup::class;
        $dql = <<<DQL
            SELECT userGroup
            FROM {$userGroupTable} userGroup
            WHERE userGroup.groupId = :groupId
        DQL;

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameter('groupId', $groupId->getValue());

        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('UserGroup not found');
        }

        return $paginator;
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    public function findGroupsUsersOrFail(array $groupsId, GROUP_ROLES $groupRole): PaginatorInterface
    {
        $userGroupTable = UserGroup::class;
        $dql = <<<DQL
            SELECT userGroup
            FROM {$userGroupTable} userGroup
            WHERE userGroup.groupId IN (:groupId)
                AND JSON_CONTAINS(userGroup.roles, :groupRole) = 1
            ORDER BY userGroup.groupId
        DQL;

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters([
                'groupId' => $groupsId,
                'groupRole' => "\"{$groupRole->value}\"",
            ]);

        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('UserGroup not found');
        }

        return $paginator;
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    public function findGroupsFirstUserByRolOrFail(array $groupsId, GROUP_ROLES $groupRole): array
    {
        $sql = <<<SQL
            SELECT
                userGroup.id,
                userGroup.group_id,
                userGroup.user_id,
                userGroup.roles
            FROM Users_Group userGroup
                RIGHT JOIN (
                    SELECT DISTINCT
                        FIRST_VALUE(ugJoin.user_id) OVER (PARTITION by ugJoin.group_id) AS first_group_user
                    FROM Users_Group AS ugJoin
                    WHERE ugJoin.group_id IN (:groupsId)
                        AND JSON_CONTAINS(ugJoin.roles, :groupRole) = 1
                ) AS groupUserTypeFirst ON groupUserTypeFirst.first_group_user = userGroup.user_id

            WHERE userGroup.group_id IN (:groupsId)
        SQL;

        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addEntityResult(UserGroup::class, 'userGroup');
        $resultSetMapping->addFieldResult('userGroup', 'id', 'id');
        $resultSetMapping->addFieldResult('userGroup', 'group_id', 'groupId');
        $resultSetMapping->addFieldResult('userGroup', 'user_id', 'userId');
        $resultSetMapping->addFieldResult('userGroup', 'roles', 'roles');

        $query = $this->entityManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters([
            'groupsId' => $groupsId,
            'groupRole' => "\"{$groupRole->value}\"",
        ]);

        $result = $query->getResult();

        if (empty($result)) {
            throw DBNotFoundException::fromMessage('Group users not found');
        }

        return $result;
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersByUserIdOrFail(Identifier $groupId, array $usersId): array
    {
        /** @var UserGroup[] $groupUsers */
        $groupUsers = $this->findBy([
            'groupId' => $groupId,
            'userId' => $usersId,
        ]);

        if (empty($groupUsers)) {
            throw DBNotFoundException::fromMessage('Group users not found');
        }

        return $groupUsers;
    }

    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersByRol(Identifier $groupId, GROUP_ROLES $groupRol): array
    {
        $userGroupEntity = UserGroup::class;
        $dql = <<<DQL
            SELECT userGroup
            FROM {$userGroupEntity} userGroup
            WHERE userGroup.groupId = :group_id
                AND JSON_CONTAINS(userGroup.roles, :rol) = 1
        DQL;

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters([
                'group_id' => $groupId,
                'rol' => '"'.$groupRol->value.'"',
            ]);

        $result = $query->getResult();

        if (empty($result)) {
            throw DBNotFoundException::fromMessage('Group users not found');
        }

        return $result;
    }

    /**
     * @throws DBNotFoundException
     */
    public function findUserGroupsById(Identifier $userId, ?GROUP_ROLES $groupRol = null, ?GROUP_TYPE $groupType = null): PaginatorInterface
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('userGroup')
            ->from(UserGroup::class, 'userGroup')
            ->where('userGroup.userId = :user_id')
            ->setParameter('user_id', $userId);

        if (null !== $groupRol) {
            $queryBuilder
                ->andWhere('JSON_CONTAINS(userGroup.roles, :rol) = 1')
                ->setParameter('rol', '"'.$groupRol->value.'"');
        }

        if (null !== $groupType) {
            $queryBuilder
                ->leftJoin(Group::class, 'groups', Join::WITH, 'userGroup.groupId = groups.id')
                ->andWhere('groups.type = :type')
                ->setParameter('type', $groupType);
        }

        $paginator = $this->paginator->createPaginator($queryBuilder);

        if (0 == $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('No groups found');
        }

        return $paginator;
    }

    /**
     * @throws DBNotFoundException
     */
    public function findUserGroupsByName(Identifier $userId, ?Filter $filterText, ?GROUP_TYPE $groupType, bool $orderAsc): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('usersGroups')
            ->from(UserGroup::class, 'usersGroups')
            ->leftJoin(Group::class, 'groups', Join::WITH, 'groups.id = usersGroups.groupId')
            ->where('usersGroups.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('groups.name', $orderAsc ? 'ASC' : 'DESC');

        if (null !== $groupType) {
            $query
                ->andWhere('groups.type = :groupType')
                ->setParameter('groupType', $groupType);
        }

        if (null !== $filterText && !$filterText->isNull()) {
            $query
                ->andWhere('groups.name LIKE :groupName')
                ->setParameter('groupName', $filterText->getValueWithFilter());
        }

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersNumberOrFail(Identifier $groupId): int
    {
        $userGroupEntity = UserGroup::class;
        $sql = <<<SQL
            SELECT COUNT(userGroup)
            FROM {$userGroupEntity} userGroup
            WHERE userGroup.groupId = :group_id
        SQL;

        $query = $this->entityManager
            ->createQuery($sql)
            ->setParameter('group_id', $groupId);

        $result = $query->getOneOrNullResult();

        if (0 === $result[1]) {
            throw DBNotFoundException::fromMessage('Group not found');
        }

        return (int) $result[1];
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    public function findGroupsUsersNumberOrFail(array $groupsId): PaginatorInterface
    {
        $userGroupEntity = UserGroup::class;
        $dql = <<<DQL
            SELECT userGroup.groupId, COUNT(userGroup) AS groupUsers
            FROM {$userGroupEntity} userGroup
            WHERE userGroup.groupId IN (:groupsId)
            GROUP BY userGroup.groupId
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupsId' => $groupsId,
        ]);
    }

    /**
     * @param UserGroup[] $usersGroup
     *
     * @throws DBConnectionException
     * @throws DBUniqueConstraintException
     */
    public function save(array $usersGroup): void
    {
        try {
            foreach ($usersGroup as $userGroup) {
                $this->objectManager->persist($userGroup);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($userGroup->getId(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param UserGroup[] $usersGroup
     */
    public function removeUsers(array $usersGroup): void
    {
        try {
            foreach ($usersGroup as $userGroup) {
                $this->objectManager->remove($userGroup);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }
}
