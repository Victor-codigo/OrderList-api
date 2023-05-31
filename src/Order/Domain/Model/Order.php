<?php

declare(strict_types=1);

namespace Order\Domain\Model;

use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

final class Order
{
    private Identifier $id;
    private Identifier $userId;
    private Identifier $groupId;
    private Identifier $productId;
    private Identifier $shopId;
    private Description $description;
    private Amount $amount;
    private \DateTime $createdOn;

    private Product $product;
    private Shop|null $shop;
    /**
     * @var Collection<ListOrders>
     */
    private Collection $listOrders;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function setUserId(Identifier $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function getProductId(): Identifier
    {
        return $this->productId;
    }

    public function getShopId(): Identifier
    {
        return $this->shopId;
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

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function setAmount(Amount $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTime $date): self
    {
        $this->createdOn = $date;

        return $this;
    }

    public function getProducts(): Product
    {
        return $this->product;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getShop(): shop
    {
        return $this->shop;
    }

    public function setShop(Shop|null $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getListOrders(): Collection
    {
        return $this->listOrders;
    }

    /**
     * @param Order[] $orders
     */
    public function setListOrders(array $orders): self
    {
        $this->listOrders = new ArrayCollection($orders);

        return $this;
    }

    public function addListOrders(Order $order): self
    {
        $this->listOrders->add($order);

        return $this;
    }

    public function __construct(Identifier $id, Identifier $userId, Identifier $groupId, Description $description, Amount $amount, Product $product, Shop|null $shop = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->productId = $product->getId();
        $this->shopId = null !== $shop
            ? $shop->getId()
            : ValueObjectFactory::createIdentifier(null);
        $this->description = $description;
        $this->amount = $amount;
        $this->createdOn = new \DateTime();

        $this->product = $product;
        $this->shop = $shop;
        $this->listOrders = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $userId, string $groupId, float $amount, string $description, Product $product, Shop|null $shop = null): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createAmount($amount),
            $product,
            $shop
        );
    }
}
