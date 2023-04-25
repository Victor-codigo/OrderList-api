<?php

declare(strict_types=1);

namespace Shop\Domain\Model;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\Group;

final class Shop
{
    private Identifier $id;
    private Identifier $groupId;
    private Name $name;
    private Money $price;
    private Description $description;
    private Path $image;
    private \DateTime $createdOn;

    private Group $group;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function setGroupId(Identifier $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function __construct(Identifier $id, Identifier $groupId, Name $name, Money $price, Description $description, Path $image)
    {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->image = $image;
        $this->createdOn = new \DateTime();
    }

    public static function fromPrimitives(string $id, string $groupId, string $name, string|null $price = null, string|null $description = null, string|null $image = null): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createName($name),
            ValueObjectFactory::createMoney($price),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createPath($image),
        );
    }
}
