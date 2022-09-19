<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use User\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function remove(User $user): void;
}
