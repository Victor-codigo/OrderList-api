<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use Common\Domain\Ports\Repository\RepositoryInterface;
use User\Domain\Model\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function save(User $user): void;

    public function remove(User $user): void;
}
