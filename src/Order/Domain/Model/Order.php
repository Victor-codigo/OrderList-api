<?php

declare(strict_types=1);

namespace Order\Domain\Model;

use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Product\Domain\Model\Product;

final class Order
{
    private Identifier $id;
    private Identifier $userId;
    private Identifier $groupId;
    private Identifier $productId;
    private Amount $amount;
    private Description $description;
    private \DateTime $createdOn;
    private \DateTime|null $boughtOn;
    private \DateTime|null $dateToBuy;

    private Product $product;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function getProductId(): Identifier
    {
        return $this->productId;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

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

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function getBoughtOn(): \DateTime
    {
        return $this->boughtOn;
    }

    public function setBoughtOn($boughtOn): self
    {
        $this->boughtOn = $boughtOn;

        return $this;
    }

    public function getDataToBuy(): \DateTime
    {
        return $this->dateToBuy;
    }

    public function setDateToBuy($dateToBuy): self
    {
        $this->dateToBuy = $dateToBuy;

        return $this;
    }

    public function getProducts(): Product
    {
        return $this->product;
    }

    public function __construct(Identifier $id, Identifier $userId, Identifier $groupId, Identifier $productId, Amount $amount, \DateTime $dateToBuy, Description $description)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->productId = $productId;
        $this->amount = $amount;
        $this->dateToBuy = $dateToBuy;
        $this->description = $description;
        $this->createdOn = new \DateTime();
    }

    public static function fromPrimitives(string $id, string $userId, string $groupId, string $productId, float $amount, \DateTime $dateToBuy, string $description): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createIdentifier($productId),
            ValueObjectFactory::createAmount($amount),
            $dateToBuy,
            ValueObjectFactory::createDescription($description),
            new \DateTime()
        );
    }
}
