<?php

declare(strict_types=1);

namespace Group\Adapter\Database\Orm\Doctrine\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Global\Adapter\Framework\IdGenerator;

final class Group
{
    private string $id;
    private string $name;
    private string|null $description;
    private DateTime $createdOn;
    private Collection $users;
    private Collection $shops;
    private Collection $products;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function __construct(string $name)
    {
        $this->id = IdGenerator::createId();
        $this->name = $name;
        $this->createdOn = new DateTime();
        $this->users = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn,
            'users' => $this->users->toArray(),
            'shops' => $this->users->toArray(),
            'products' => $this->users->toArray(),
        ];
    }
}
