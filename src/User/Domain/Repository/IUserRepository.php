<?php

declare(strict_types=1);

namespace User\Domain\Repository;

use User\Domain\Model\User;

interface IUserRepository
{
    public function save(User $user): self;

    public function remove(User $user): self;
}
