<?php

declare(strict_types=1);

namespace User\Domain\Event;

use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class UserPreRegisteredEvent implements EventDomainInterface
{
    private readonly Identifier $id;

    public function __construct(Identifier $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id->getValue();
    }

    public function __invoke(): void
    {
        $r = 0;
    }
}
