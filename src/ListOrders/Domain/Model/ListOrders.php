<?php

declare(strict_types=1);

namespace ListOrders\Domain\Model;

use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Order\Domain\Model\Order;

class ListOrders
{
    private Identifier $id;
    private Identifier $userId;
    private Identifier $groupId;
    private NameWithSpaces $name;
    private Description $description;
    private DateNowToFuture $dateToBuy;
    private \DateTime $createdOn;

    /**
     * @var Collection<Order>
     */
    private Collection $orders;
    /**
     * @var Collection<Share>
     */
    private Collection $shares;

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

    public function getDateToBuy(): DateNowToFuture
    {
        return $this->dateToBuy;
    }

    public function setDateToBuy(DateNowToFuture $date): self
    {
        $this->dateToBuy = $date;

        return $this;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * @param Order[] $orders
     */
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

    public function __construct(Identifier $id, Identifier $groupId, Identifier $userId, NameWithSpaces $name, Description $description, DateNowToFuture $dateToBuy)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->name = $name;
        $this->description = $description;
        $this->dateToBuy = $dateToBuy;
        $this->createdOn = new \DateTime();

        $this->orders = new ArrayCollection();
        $this->shares = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $groupId, string $userId, string $name, ?string $description, ?\DateTime $dateToBuy): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($groupId),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createDescription($description),
            ValueObjectFactory::createDateNowToFuture($dateToBuy),
        );
    }
}
