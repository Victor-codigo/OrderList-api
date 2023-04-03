<?php

namespace Notification\Domain\Model;

use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;

final class Notification
{
    private Identifier $id;
    private Identifier $userId;
    private NotificationType $type;
    private bool $viewed;
    private \DateTime $createdOn;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getUserId(): Identifier
    {
        return $this->userId;
    }

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function setType(NotificationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getViewed(): bool
    {
        return $this->viewed;
    }

    public function setViewed(bool $viewed): void
    {
        $this->viewed = $viewed;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function __construct(Identifier $id, Identifier $userId, NotificationType $type)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->viewed = false;
        $this->createdOn = new \DateTime();
    }

    public static function fromPrimitives(string $id, string $userId, NOTIFICATION_TYPE $type): self
    {
        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createIdentifier($userId),
            ValueObjectFactory::createNotificationType($type)
        );
    }
}
