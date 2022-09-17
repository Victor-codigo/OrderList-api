<?php

declare(strict_types=1);

namespace User\Domain\Repository\Port;

use User\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): self;

    public function remove(User $user): self;
}
