<?php

declare(strict_types=1);

namespace Share\Domain\Model;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;

class Share
{
    private Identifier $id;
    private Identifier $listOrdersId;
    private Identifier $groupId;
    private Identifier $userId;
    private \DateTime $expire;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getListOrdersId(): Identifier
    {
        return $this->listOrdersId;
    }

    public function getGroupId(): Identifier
    {
        return $this->groupId;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function getExpire(): \DateTime
    {
        return $this->expire;
    }

    public function __construct(Identifier $id, Identifier $userId, Identifier $listOrdersId, Identifier $groupId, \DateTime $expire)
    {
        $this->id = $id;
        $this->listOrdersId = $listOrdersId;
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->expire = $expire;
    }

    public static function fromPrimitives(string $id, string $userId, string $listOrdersId, string $groupId, \DateTime $expire): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createIdentifier($listOrdersId),
            ValueObjectFactory::createIdentifier($groupId),
            $expire
        );
    }
}
