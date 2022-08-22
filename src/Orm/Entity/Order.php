<?php

namespace App\Orm\Entity;

use DateTime;

final class Order
{
    protected string $id;
    protected string $userId;
    protected string $groupId;
    protected string $productId;
    protected bool $deleted;
    protected float|null $price;
    protected float|null $amount;
    protected string|null $description;
    protected DateTime $createdOn;
    protected DateTime|null $bougthOn;
    protected DateTime|null $buyOn;


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

    public function getBougthOn(): DateTime
    {
        return $this->bougthOn;
    }

    public function setBougthOn($bougthOn): self
    {
        $this->bougthOn = $bougthOn;

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

    public function __construct(string $id, string $userId, string $groupId, string $productId, bool $deleted)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->productId = $productId;
        $this->deleted = $deleted;
        $this->createdOn = new DateTime();
    }
}
