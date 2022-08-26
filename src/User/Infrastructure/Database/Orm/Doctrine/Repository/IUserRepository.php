<?php

declare(strict_types=1);

namespace User\Infrastructure\Database\Orm\Doctrine\Repository;

use User\Domain\Model\EntityBase as EntityBaseDomain;

interface IUserRepository
{
    public function save(EntityBaseDomain $user): void;

    public function remove(EntityBaseDomain $user): void;
}
