<?php

declare(strict_types=1);

namespace User\Domain\Port\User;

use User\Domain\Model\User;

interface UserInterface
{
    public function passwordHash(): self;

    public function passwordIsValid(string $plainPassword): bool;

    public function getUser(): User;

    public function setUser(User $user): self;
}
