<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String\IdentifierType;
use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
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
    public function findGroupUsersOrFail(Identifier $groupId): array
    {
        /** @var UserGroup[] $groupUsers */
        $groupUsers = $this->findBy(['groupId' => $groupId]);

        if (empty($groupUsers)) {
            throw DBNotFoundException::fromMessage('UserGroup not found');
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
        $usersGroup = $this->findGroupUsersOrFail($groupId);
        $adminRol = ValueObjectFactory::createRol($groupRol);

        return array_filter(
            $usersGroup,
            fn (UserGroup $userGroup) => $userGroup->getRoles()->has($adminRol)
        );
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersNumberOrFail(Identifier $groupId): int
    {
        $sql = <<<sql
            SELECT COUNT(userGroup)
            FROM {$this->getString(UserGroup::class)} userGroup
            WHERE userGroup.groupId = :group_id
        sql;

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
}
