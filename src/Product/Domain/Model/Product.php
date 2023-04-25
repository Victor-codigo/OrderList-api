<?php

declare(strict_types=1);

namespace Product\Domain\Model;

use  Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Product
{
    private Identifier $id;
    private Name $name;
    private Money $price;
    private Description $description;
    private Path $image;
    private \DateTime $createdOn;

    /**
     * @var Collection<Order>
     */
    private Collection $orders;

    /**
     * @var Collection<Shop>
     */
    private Collection $shops;

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

    public function getImage(): Path
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn($createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return Collection<Shop>
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    /**
     * @return Collection<Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function __construct(Identifier $id, Name $name, Money $price, Description $description, Path $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->image = $image;
        $this->createdOn = new \DateTime();
        $this->orders = new ArrayCollection();
        $this->shops = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $name, float $price, string $description, string $image): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createName($name),
            ValueObjectFactory::createMoney($price),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createPath($image),
        );
    }
}
