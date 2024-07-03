<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt\Fixtures;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use User\Domain\Model\User;
use User\Domain\Port\User\UserInterface;

class UserAdapter implements PasswordAuthenticatedUserInterface, UserInterface, SymfonyUserInterface
{
    #[\Override]
    public function getPassword(): ?string
    {
        return '';
    }

    #[\Override]
    public function getUser(): User
    {
        return User::fromPrimitives('', '', '', '', []);
    }

    #[\Override]
    public function setUser(User $user): self
    {
        return new self();
    }

    #[\Override]
    public function getRoles(): array
    {
        return [];
    }

    #[\Override]
    public function eraseCredentials()
    {
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return '';
    }
}
