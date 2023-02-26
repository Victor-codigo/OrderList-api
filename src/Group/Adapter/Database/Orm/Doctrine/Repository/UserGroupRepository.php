<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String\IdentifierType;
use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Doctrine\Persistence\ManagerRegistry;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class UserGroupRepository extends RepositoryBase implements UserGroupRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, UserGroup::class);
    }

    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersOrFail(Identifier $groupId, int $limit = null, int $offset = null): array
    {
        /** @var UserGroup[] $groupUsers */
        $groupUsers = $this->findBy(['groupId' => $groupId], null, $limit, $offset);

        if (empty($groupUsers)) {
            throw DBNotFoundException::fromMessage('UserGroup not found');
        }

        return $groupUsers;
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
        $dql = <<<DQL
            SELECT userGroup
            FROM {$this->getString(UserGroup::class)} userGroup
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
    public function findUserGroupsById(Identifier $userId, GROUP_ROLES|null $groupRol = null): array
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

        $result = $queryBuilder
            ->getQuery()
            ->getResult();

        if (empty($result)) {
            throw DBNotFoundException::fromMessage('No groups found');
        }

        return $result;
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersNumberOrFail(Identifier $groupId): int
    {
        $sql = <<<SQL
            SELECT COUNT(userGroup)
            FROM {$this->getString(UserGroup::class)} userGroup
            WHERE userGroup.groupId = :group_id
        SQL;

        $query = $this->entityManager
            ->createQuery($sql)
            ->setParameter('group_id', $groupId, $this->getClassUnqualifiedName(IdentifierType::class));

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
