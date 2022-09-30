<?php

declare(strict_types=1);

namespace User\Domain\Port\User;

use User\Domain\Model\User;

interface UserInterface
{
    public function getUser(): User;

    public function setUser(User $user): self;
}
