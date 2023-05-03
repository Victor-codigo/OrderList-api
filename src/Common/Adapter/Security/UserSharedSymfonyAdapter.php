<?php

declare(strict_types=1);

namespace Common\Adapter\Security;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Security\UserShared;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSharedSymfonyAdapter implements UserInterface, UserSharedInterface
{
    public function __construct(
        private UserShared $user
    ) {
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->user
            ->getRoles()
            ->getValue();

        return array_map(
            fn (Rol $rol) => $rol->getValue()->value,
            $roles ?? []
        );
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getId()->getValue();
    }

    public function getUser(): UserShared
    {
        return $this->user;
    }

    public function setUser(UserShared $user): self
    {
        $this->user = $user;

        return $this;
    }
}
