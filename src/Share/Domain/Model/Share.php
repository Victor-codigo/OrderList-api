<?php

declare(strict_types=1);

namespace Share\Domain\Model;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use User\Domain\Model\User;

class Share
{
    private Identifier $id;
    private Identifier $listOrdersId;
    private Identifier $userId;
    private \DateTime $expire;

    private User $user;
    private ListOrders $listOrders;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getListOrdersId(): Identifier
    {
        return $this->listOrdersId;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function getExpire(): \DateTime
    {
        return $this->expire;
    }

    public function __construct(Identifier $id, ListOrders $listOrders, User $user, \DateTime $expire)
    {
        $this->id = $id;
        $this->listOrdersId = $listOrders->getId();
        $this->userId = $user->getId();
        $this->expire = $expire;
        $this->listOrders = $listOrders;
        $this->user = $user;
    }

    public static function fromPrimitives(string $id, ListOrders $listOrders, User $user, \DateTime $expire): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            $listOrders,
            $user,
            $expire
        );
    }
}
