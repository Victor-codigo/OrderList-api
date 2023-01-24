<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\Model\User;

final class Group
{
    private Identifier $id;
    private Name $name;
    private Description $description;
    private \DateTime $createdOn;
    private GroupType $type;
    private Collection $users;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDescription(Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function getType(): GroupType
    {
        return $this->type;
    }

    public function setType(GroupType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param UserGroup[] $usersGroup
     */
    public function setUsers(array $usersGroup): self
    {
        $this->users = $usersGroup;

        return $this;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        $this->users->add($userGroup);

        return $this;
    }

    public function __construct(Identifier $id, Name $name, GroupType $type, Description $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->users = new ArrayCollection();
        $this->createdOn = new \DateTime();
    }

    public static function fromPrimitives(string $id, string $name, GROUP_TYPE $type, string|null $description): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createName($name),
            ValueObjectFactory::createGroupType($type),
            ValueObjectFactory::createDescription($description)
        );
    }
}
