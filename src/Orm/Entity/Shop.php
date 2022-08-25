<?php

declare(strict_types=1);

namespace App\Orm\Entity;

use Adapter\Framework\IdAdapter;
use DateTime;

final class Shop implements IEntity
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
        $this->id = IdAdapter::createId();
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
