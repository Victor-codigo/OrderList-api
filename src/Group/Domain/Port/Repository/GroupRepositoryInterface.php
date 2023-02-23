<?php

declare(strict_types=1);

namespace Group\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Group\Domain\Model\Group;

interface GroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Group $group): void;

    /**
     * @throws DBConnectionException
     */
    public function remove(Group $group): void;

    /**
     * @param Identifier[] $ids
     *
     * @return Group[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupsByIdOrFail(array $id): array;
}
