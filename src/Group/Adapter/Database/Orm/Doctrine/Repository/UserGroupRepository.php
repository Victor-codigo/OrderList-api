<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\ORM\Query\Expr\Join;
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
    public function findUserGroupsById(Identifier $userId, GROUP_ROLES $groupRol = null, GROUP_TYPE $groupType = null): PaginatorInterface
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
     * @param UserGroup[] $usersGroup
     *
     * @throws DBConnectionException
     */
    public function save(array $usersGroup): void
    {
        try {
            foreach ($usersGroup as $userGroup) {
                $this->objectManager->persist($userGroup);
            }

            $this->objectManager->flush();
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
