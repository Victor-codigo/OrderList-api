<?php

declare(strict_types=1);

namespace Shop\Adapter\Database\Orm\Doctrine\Entity;

use Common\Adapter\IdGenerator\IdGenerator;
use DateTime;
use Group\Adapter\Database\Orm\Doctrine\Entity\Group;

final class Shop
{
    private string $id;
    private string $name;
    private string|null $description = null;
    private DateTime $createdOn;
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

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function __construct(Group $group, string $name, string $description)
    {
        $this->id = IdGenerator::createId();
        $this->name = $name;
        $this->description = $description;
        $this->createdOn = new DateTime();
        $this->group = $group;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn,
            'group' => $this->group->toArray(),
        ];
    }
}
