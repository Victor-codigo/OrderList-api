<?php

declare(strict_types=1);

namespace User\Repository;

use User\Orm\Entity\IUserEntity;

interface IUserRepository
{
    public function save(IUserEntity $user): void;

    public function remove(IUserEntity $user): void;
}
