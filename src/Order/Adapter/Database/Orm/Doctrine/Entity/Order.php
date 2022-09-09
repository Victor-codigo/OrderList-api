<?php

namespace Order\Adapter\Database\Orm\Doctrine\Entity;

use Common\Adapter\IdGenerator\IdGenerator;
use DateTime;

final class Order
{
    private string $id;
    private string $userId;
    private string $groupId;
    private string $productId;
    private bool $deleted;
    private float|null $price;
    private float|null $amount;
    private string|null $description;
    private DateTime $createdOn;
    private DateTime|null $boughtOn;
    private DateTime|null $buyOn;

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getDeleted(): string
    {
        return $this->deleted;
    }

    public function setDeleted($deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getPrice(): float|null
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAmount(): float|null
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBoughtOn(): DateTime
    {
        return $this->boughtOn;
    }

    public function setBoughtOn($boughtOn): self
    {
        $this->boughtOn = $boughtOn;

        return $this;
    }

    public function getBuyOn(): DateTime
    {
        return $this->buyOn;
    }

    public function setBuyOn($buyOn): self
    {
        $this->buyOn = $buyOn;

        return $this;
    }

    public function __construct(string $userId, string $groupId, string $productId, bool $deleted)
    {
        $this->id = IdGenerator::createId();
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->productId = $productId;
        $this->deleted = $deleted;
        $this->createdOn = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'groupId' => $this->groupId,
            'productId' => $this->productId,
            'deleted' => $this->deleted,
            'price' => $this->price,
            'amount' => $this->amount,
            'description' => $this->description,
            'createdOn' => $this->createdOn->format(DateTime::RFC3339),
            'buyOn' => $this->buyOn->format(DateTime::RFC3339),
            'boughtOn' => $this->boughtOn->format(DateTime::RFC3339),
        ];
    }
}
