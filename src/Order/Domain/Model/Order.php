<?php

declare(strict_types=1);

namespace Order\Domain\Model;

use DateTime;
use ReflectionClass;
use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class Order
{
    private Identifier $id;
    private Identifier $listOrdersId;
    private Identifier $groupId;
    private Identifier $userId;
    private Identifier $productId;
    private Identifier $shopId;
    private Description $description;
    private Amount $amount;
    private bool $bought;
    private DateTime $createdOn;

    private ListOrders $listOrders;
    private Product $product;
    private ?Shop $shop;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function getListOrdersId(): Identifier
    {
        return $this->listOrdersId;
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

    public function setGroupId(Identifier $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getProductId(): Identifier
    {
        return $this->productId;
    }

    public function setProductId(Identifier $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getShopId(): Identifier
    {
        return $this->shopId;
    }

    public function setShopId(Identifier $shopId): self
    {
        $this->shopId = $shopId;

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

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function setAmount(Amount $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBought(): bool
    {
        return $this->bought;
    }

    public function setBought(bool $bought): self
    {
        $this->bought = $bought;

        return $this;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn(DateTime $date): self
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

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getListOrders(): ListOrders
    {
        return $this->listOrders;
    }

    public function setListOrders(ListOrders $listOrders): self
    {
        $this->listOrders = $listOrders;
        $this->listOrdersId = $this->listOrders->getId();

        return $this;
    }

    public function __construct(Identifier $id, Identifier $groupId, Identifier $userId, Description $description, Amount $amount, bool $bought, ListOrders $listOrders, Product $product, ?Shop $shop = null)
    {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->listOrdersId = $listOrders->getId();
        $this->userId = $userId;
        $this->productId = $product->getId();
        $this->shopId = null !== $shop
            ? $shop->getId()
            : ValueObjectFactory::createIdentifier(null);
        $this->description = $description;
        $this->amount = $amount;
        $this->bought = $bought;
        $this->createdOn = new DateTime();

        $this->listOrders = $listOrders;
        $this->product = $product;
        $this->shop = $shop;
    }

    public static function fromPrimitives(string $id, string $groupId, string $userId, ?string $description, float $amount, bool $bought, ListOrders $listOrders, Product $product, ?Shop $shop = null): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createAmount($amount),
            $bought,
            $listOrders,
            $product,
            $shop
        );
    }

    public function cloneWithNewId(Identifier $id): Order
    {
        $orderNew = clone $this;
        $orderNewReflection = new ReflectionClass($orderNew);
        $idProperty = $orderNewReflection->getProperty('id');
        $idProperty->setValue($orderNew, $id);

        return $orderNew;
    }
}
