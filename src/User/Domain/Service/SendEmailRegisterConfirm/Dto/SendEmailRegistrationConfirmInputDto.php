<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm\Dto;

class SendEmailRegistrationConfirmInputDto
{
    public readonly string $id;
    public readonly string $emailTo;

    public function __construct(string $id, string $emailTo)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;
    }
}
