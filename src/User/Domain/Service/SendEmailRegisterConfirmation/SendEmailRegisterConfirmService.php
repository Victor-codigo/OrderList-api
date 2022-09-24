<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm;

use Common\Domain\Ports\Mailer\MailerInterface;
use User\Domain\Service\SendEmailRegisterConfirmation\Dto\SendEmailRegisterConfirmInputDto;

class SendEmailRegisterConfirmService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(SendEmailRegisterConfirmInputDto $emailInfo)
    {
        $this->mailer
            ->subject('')
            ->from('')
            ->to($emailInfo->to)
            ->send();
    }
}
