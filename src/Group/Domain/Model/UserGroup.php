<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\Collection;

class UserGroup
{
    private int $id;
    private Collection $groups;

    public function __construct(
        private Identifier $groupId,
        private Identifier $userId,
        private Roles $roles
    ) {
    }

    public static function fromPrimitives(string $userId, string $groupId, array $roles): self
    {
        $roles = array_map(
            fn (GROUP_ROLES $rol) => ValueObjectFactory::createRol($rol),
            $roles
        );

        return new self(
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createRoles($roles),
        );
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function setUserId(Identifier $id): self
    {
        $this->userId = $id;

        return $this;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function setGroupId(Identifier $id): self
    {
        $this->groupId = $id;

        return $this;
    }

    public function getRoles(): Roles
    {
        return $this->roles;
    }

    public function setRoles(Roles $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
