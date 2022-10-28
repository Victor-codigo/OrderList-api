<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;

class SendEmailPasswordRememberDto
{
    public readonly Identifier $id;
    public readonly Email $emailTo;
    public readonly Name $userName;

    public function __construct(Identifier $id, Email $emailTo, Name $userName)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;
        $this->userName = $userName;
    }
}
