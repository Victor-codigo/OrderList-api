<?php

declare(strict_types=1);

namespace Shop\Domain\Model;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageModifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Order\Domain\Model\Order;
use Product\Domain\Model\ProductShop;

class Shop implements EntityImageModifyInterface
{
    private Identifier $id;
    private Identifier $groupId;
    private NameWithSpaces $name;
    private Description $description;
    private Path $image;
    private \DateTime $createdOn;

    /**
     * @var Collection<ProductShop>
     */
    private Collection $productShop;
    /**
     * @var Collection<Shop>
     */
    private Collection $orders;

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

    public function setName(NameWithSpaces $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function setDescription(Description $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): Path
    {
        return $this->image;
    }

    public function setImage(Path $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    /**
     * @return Collection<ProductShop>
     */
    public function getProductShop(): Collection
    {
        return $this->productShop;
    }

    /**
     * @param ProductShop[] $productShops
     */
    public function setProductShops(array $productShops): self
    {
        $this->productShop = new ArrayCollection($productShops);

        return $this;
    }

    public function addProductShop(ProductShop $productShop): self
    {
        $this->productShop->add($productShop);

        return $this;
    }

    /**
     * @return Collection<Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function setOrders(array $orders): self
    {
        $this->orders = new ArrayCollection($orders);

        return $this;
    }

    public function addOrder(Order $order): self
    {
        $this->orders->add($order);

        return $this;
    }

    public function __construct(Identifier $id, Identifier $groupId, NameWithSpaces $name, Description $description, Path $image)
    {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->createdOn = new \DateTime();

        $this->productShop = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $groupId, string $name, ?string $description = null, ?string $image = null): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createPath($image),
        );
    }

    public static function fromPrimitiveArrayOfData(array $data): self
    {
        if (!isset($data['id'])
        || !isset($data['group_id'])
        || !isset($data['name'])) {
            throw LogicException::fromMessage('Not enough data parameters to create a Shop');
        }

        return self::fromPrimitives(
            $data['id'],
            $data['group_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['image'] ?? null,
        );
    }
}
