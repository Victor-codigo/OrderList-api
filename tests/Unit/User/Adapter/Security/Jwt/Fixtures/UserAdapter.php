<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt\Fixtures;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use User\Domain\Model\User;
use User\Domain\Port\User\UserInterface;

class UserAdapter implements PasswordAuthenticatedUserInterface, UserInterface, SymfonyUserInterface
{
    public function getPassword(): ?string
    {
        return '';
    }

    public function getUser(): User
    {
        return User::fromPrimitives('', '', '', '', []);
    }

    public function setUser(User $user): self
    {
        return new self();
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return '';
    }
}
