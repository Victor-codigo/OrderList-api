<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use DateTime;
use Global\Infrastructure\Framework\IdGenerator;
use User\Domain\Model\User;

final class Group
{
    private string $id;
    private string $name;
    private string|null $description;
    private DateTime $createdOn;
    private array $users;
    private array $shops;
    private array $products;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUsers(): iterable
    {
        return $this->users;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getShops(): iterable
    {
        return $this->shops;
    }

    public function getProducts(): iterable
    {
        return $this->products;
    }

    public function __construct(string $name)
    {
        $this->id = IdGenerator::createId();
        $this->name = $name;
        $this->createdOn = new DateTime();
        $this->users = [];
        $this->shops = [];
        $this->products = [];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdOn' => $this->createdOn,
            'users' => array_map(fn (User $i) => $i->toArray(), $this->users),
            'shops' => array_map(fn (User $i) => $i->toArray(), $this->users),
            'products' => array_map(fn (User $i) => $i->toArray(), $this->users),
        ];
    }
}
