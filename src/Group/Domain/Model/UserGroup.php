<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;

class UserGroup
{
    private int $id;

    public function __construct(
        private Identifier $groupId,
        private Identifier $userId,
        private Roles $roles,
        private Group $group
    ) {
    }

    /**
     * @param Rol[]         $roles
     * @param GROUP_ROLES[] $roles
     */
    public static function fromPrimitives(string $groupId, string $userId, array $roles, group $group): self
    {
        $roles = array_map(
            fn (GROUP_ROLES $rol) => ValueObjectFactory::createRol($rol),
            $roles
        );

        return new self(
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createRoles($roles),
            $group
        );
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }
}
