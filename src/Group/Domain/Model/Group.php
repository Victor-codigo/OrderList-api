<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\Model\User;

class Group
{
    private Identifier $id;
    private Name $name;
    private Description $description;
    private Path $image;
    private \DateTime $createdOn;
    private GroupType $type;

    /**
     * @var Collection<User>
     */
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

    public function setImage(Path $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): Path
    {
        return $this->image;
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
        $this->users = new ArrayCollection($usersGroup);

        return $this;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        $this->users->add($userGroup);

        return $this;
    }

    public function __construct(Identifier $id, Name $name, GroupType $type, Description $description, Path $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->image = $image;
        $this->users = new ArrayCollection();
        $this->createdOn = new \DateTime();
    }

    public static function fromPrimitives(string $id, string $name, GROUP_TYPE $type, string|null $description, string|null $image): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createName($name),
            ValueObjectFactory::createGroupType($type),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createPath($image)
        );
    }
}
