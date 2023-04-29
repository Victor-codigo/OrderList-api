<?php

declare(strict_types=1);

namespace Product\Domain\Model;

use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Product
{
    private Identifier $id;
    private Identifier $groupId;
    private NameWithSpaces $name;
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

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function getName(): NameWithSpaces
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

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

    public function __construct(Identifier $id, identifier $groupId, NameWithSpaces $name, Description $description, Path $image)
    {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->createdOn = new \DateTime();
        $this->orders = new ArrayCollection();
        $this->shops = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $groupId, string $name, string|null $description = null, string|null $image = null): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createPath($image),
        );
    }
}
