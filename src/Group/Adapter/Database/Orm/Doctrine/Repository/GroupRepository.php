<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;

class GroupRepository extends RepositoryBase implements GroupRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Group::class);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Group $group): void
    {
        try {
            $this->objectManager->persist($group);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($group->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBConnectionException
     */
    public function remove(Group $group): void
    {
        try {
            $this->objectManager->remove($group);
            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier[] $ids
     *
     * @return Group[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupsByIdOrFail(array $groupsId): array
    {
        /** @var Group[] $groups */
        $groups = $this->findBy(['id' => $groupsId]);

        if (empty($groups)) {
            throw DBNotFoundException::fromMessage('Groups not found');
        }

        return $groups;
    }
}
