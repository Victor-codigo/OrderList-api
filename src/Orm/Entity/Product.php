<?php

namespace App\Orm\Entity;

use DateTime;

final class Product implements IEntity
{
    private string $id;
    private string $name;
    private string $description;
    private DateTime $createdOn;

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

    public function __construct(string $id, string $name, string $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->createdOn = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn->format(DateTime::RFC3339),
        ];
    }
}
