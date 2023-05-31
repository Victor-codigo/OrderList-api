<?php

declare(strict_types=1);

namespace ListOrders\Domain\Model;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Order\Domain\Model\Order;

class ListOrdersOrders
{
    private Identifier $id;
    private Identifier $orderId;
    private Identifier $listOrdersId;
    private bool $bought;

    private ListOrders $listOrders;
    private Order $order;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getOrderId(): Identifier
    {
        return $this->orderId;
    }

    public function getListOrdersId(): Identifier
    {
        return $this->listOrdersId;
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

    public function getListOrders(): ListOrders
    {
        return $this->listOrders;
    }

    public function setListOrders(ListOrders $listOrders): self
    {
        $this->listOrders = $listOrders;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function __construct(Identifier $id, Identifier $orderId, Identifier $listOrdersId, bool $bought, ListOrders $listOrders, Order $order)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->listOrdersId = $listOrdersId;
        $this->bought = $bought;
        $this->listOrders = $listOrders;
        $this->order = $order;
    }

    public static function fromPrimitives(string $id, string $orderId, string $listOrdersId, bool $bought, ListOrders $listOrders, Order $order): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($orderId),
            ValueObjectFactory::createIdentifier($listOrdersId),
            $bought,
            $listOrders,
            $order
        );
    }
}
