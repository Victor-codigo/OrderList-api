<?php

declare(strict_types=1);

namespace Product\Domain\Model;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Shop\Domain\Model\Shop;

class ProductShop
{
    private int $id;
    private Identifier $productId;
    private Identifier $shopId;
    private Money $price;
    private UnitMeasure $unit;

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

    public function getUnit(): UnitMeasure
    {
        return $this->unit;
    }

    public function setUnit(UnitMeasure $unit): self
    {
        $this->unit = $unit;

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

    public function __construct(Product $product, Shop $shop, Money $price, UnitMeasure $unit)
    {
        $this->product = $product;
        $this->shop = $shop;
        $this->productId = $product->getId();
        $this->shopId = $shop->getId();
        $this->price = $price;
        $this->unit = $unit;
    }

    public static function fromPrimitives(Product $product, Shop $shop, float|null $price = null, UNIT_MEASURE_TYPE $unit): self
    {
        return new self(
            $product,
            $shop,
            ValueObjectFactory::createMoney($price),
            ValueObjectFactory::createUnit($unit)
        );
    }
}
