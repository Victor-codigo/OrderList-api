<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Url;

class SendEmailRegistrationConfirmInputDto
{
    public readonly Identifier $id;
    public readonly Email $emailTo;
    public readonly Url $userRegisterEmailConfirmationUrl;

    public function __construct(Identifier $id, Email $emailTo, Url $userRegisterEmailConfirmationUrl)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;
        $this->userRegisterEmailConfirmationUrl = $userRegisterEmailConfirmationUrl;
    }
}
