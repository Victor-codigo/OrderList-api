<?php

declare(strict_types=1);

namespace Order\Domain\Model;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;

class ListOrdersOrders
{
    private Identifier $id;
    private Identifier $orderId;
    private Identifier $listOrderId;
    private bool $bought;

    private ListOrders $listOrder;
    private Order $order;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getOrderId(): Identifier
    {
        return $this->orderId;
    }

    public function getListOrderId(): Identifier
    {
        return $this->listOrderId;
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

    public function __construct(Identifier $id, Identifier $orderId, Identifier $listOrderId, bool $bought = false)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->listOrderId = $listOrderId;
        $this->bought = $bought;
    }

    public static function fromPrimitives(string $id, string $orderId, string $listOrderId, bool $bought): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($orderId),
            ValueObjectFactory::createIdentifier($listOrderId),
            $bought
        );
    }
}
