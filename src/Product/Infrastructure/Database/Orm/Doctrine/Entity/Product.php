<?php

namespace Product\Infrastructure\Database\Orm\Doctrine\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Global\Infrastructure\Framework\IdGenerator;
use Group\Infrastructure\Database\Orm\Doctrine\Entity\Group;

final class Product
{
    private string $id;
    private string $name;
    private string $description;
    private DateTime $createdOn;
    private Collection $shops;
    private Group $group;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn($createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function getGroups(): Group
    {
        return $this->group;
    }

    public function __construct(Group $group, string $name, string $description)
    {
        $this->id = IdGenerator::createId();
        $this->name = $name;
        $this->description = $description;
        $this->createdOn = new DateTime();
        $this->shops = new ArrayCollection();
        $this->group = $group;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn->format(DateTime::RFC3339),
            'shops' => $this->shops->toArray(),
            'group' => $this->group->toArray(),
        ];
    }
}
