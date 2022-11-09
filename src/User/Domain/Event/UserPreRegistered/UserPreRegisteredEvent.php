<?php

declare(strict_types=1);

namespace User\Domain\Event\UserPreRegistered;

use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Url;

class UserPreRegisteredEvent implements EventDomainInterface
{
    private \DateTimeImmutable $occurredOn;

    public readonly Identifier $id;
    public readonly Email $emailTo;
    public readonly Url $userRegisterEmailConfirmationUrl;

    public function __construct(Identifier $id, Email $emailTo, Url $userRegisterEmailConfirmationUrl)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;
        $this->userRegisterEmailConfirmationUrl = $userRegisterEmailConfirmationUrl;

        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getOccurreddOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
