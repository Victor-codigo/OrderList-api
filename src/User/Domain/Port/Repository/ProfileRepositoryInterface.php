<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

interface ProfileRepositoryInterface
{
    /**
     * @param Identifier $id
     *
     * @return Profile[]
     */
    public function findProfilesOrFail(array $id): array;
}
