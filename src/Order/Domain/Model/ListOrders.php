<?php

declare(strict_types=1);

namespace Order\Domain\Model;

use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ListOrders
{
    private Identifier $id;
    private Identifier $userId;
    private NameWithSpaces $name;
    private Description $description;
    private \DateTime $dateToBuy;
    private \DateTime $createdOn;

    private Collection $orders;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
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

    public function getDateToBuy(): \DateTime
    {
        return $this->dateToBuy;
    }

    public function setDateToBuy(\DateTime $date): self
    {
        $this->dateToBuy = $date;

        return $this;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function __construct(Identifier $id, Identifier $userId, NameWithSpaces $name, Description $description, \DateTime $dateToBuy)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->description = $description;
        $this->dateToBuy = $dateToBuy;
        $this->createdOn = new \DateTime();

        $this->orders = new ArrayCollection();
    }

    public static function fromPrimitives(string $id, string $userId, string $name, string $description, \DateTime $dateToBuy)
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createDescription($description),
            $dateToBuy,
        );
    }
}
