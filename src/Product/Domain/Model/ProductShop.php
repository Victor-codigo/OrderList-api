<?php

declare(strict_types=1);

namespace Product\Domain\Model;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Shop\Domain\Model\Shop;

class ProductShop
{
    private int $id;
    private Identifier $productId;
    private Identifier $shopId;
    private Money $price;

    private Product $product;
    private Shop $shop;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): Identifier
    {
        return $this->productId;
    }

    public function getShopId(): Identifier
    {
        return $this->shopId;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): self
    {
        $this->price = $price;

        return $this;
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

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function __construct(Product $product, Shop $shop, Money $price)
    {
        $this->product = $product;
        $this->shop = $shop;
        $this->productId = $product->getId();
        $this->shopId = $shop->getId();
        $this->price = $price;
    }
}
