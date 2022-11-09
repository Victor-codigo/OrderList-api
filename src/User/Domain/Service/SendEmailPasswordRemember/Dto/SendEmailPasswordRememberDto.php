<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Url;

class SendEmailPasswordRememberDto
{
    public readonly Identifier $id;
    public readonly Email $emailTo;
    public readonly Name $userName;
    public readonly Url $passwordRememberUrl;

    public function __construct(Identifier $id, Email $emailTo, Name $userName, Url $passwordRememberUrl)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;
        $this->userName = $userName;
        $this->passwordRememberUrl = $passwordRememberUrl;
    }
}
