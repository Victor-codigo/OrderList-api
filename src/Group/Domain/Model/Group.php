<?php

declare(strict_types=1);

namespace Group\Domain\Model;

use Common\Adapter\Framework\IdGenerator;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use DateTime;
use User\Domain\Model\User;

final class Group
{
    private Identifier $id;
    private Name $name;
    private string|null $description;
    private DateTime $createdOn;
    private array $users;
    private array $shops;
    private array $products;

    public function getId(): Identifier
    {
        return $this->id;
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
            'id' => $this->id->getValue(),
            'name' => $this->name->getValue(),
            'description' => $this->description,
            'createdOn' => $this->createdOn,
            'users' => array_map(fn (User $i) => $i->toArray(), $this->users),
            'shops' => array_map(fn (User $i) => $i->toArray(), $this->users),
            'products' => array_map(fn (User $i) => $i->toArray(), $this->users),
        ];
    }
}
