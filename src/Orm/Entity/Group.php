<?php

namespace App\Orm\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Group implements IEntity
{
    private string $id;
    private string $name;
    private string|null $description;
    private DateTime $createdOn;
    private Collection $users;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function __construct(string $id, string $name, string|null $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->createdOn = new DateTime();
        $this->users = new ArrayCollection();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn,
        ];
    }
}
