<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use User\Domain\Model\Profile;

interface ProfileRepositoryInterface
{
    /**
     * @param Identifier[] $id
     *
     * @return Profile[]
     */
    public function findProfilesOrFail(array $id): array;
}
