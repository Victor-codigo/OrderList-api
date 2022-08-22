<?php

namespace App\Orm\Entity;

use DateTime;

final class Group
{
    private string $id;
    private string $name;
    private string|null $description;
    private DateTime $createdOn;
    private array $users;

    public function __construct(string $id, string $name, string|null $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->createdOn = new DateTime();
    }

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

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
