<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm;

use Common\Domain\HtmlTemplate\TemplateId;
use Common\Domain\Mailer\EmailDto;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Ports\Mailer\MailerInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use User\Adapter\Templates\Email\EmailRegistrationConfirmation\EmailRegistrationConfirmationDto;
use User\Domain\Service\SendEmailRegisterConfirm\Dto\SendEmailRegistrationConfirmInputDto;

class SendEmailRegistrationConfirmationService
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;
    private JwtHS256Interface $jwt;
    private DIInterface $DI;
    private string $adminEmail;
    private string $appName;
    private int $emailUserRegistrationConfirmationExpire;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        JwtHS256Interface $jwt,
        DIInterface $DI,
        string $adminEmail,
        string $appName,
        int $emailUserRegistrationConfirmationExpire
        ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->jwt = $jwt;
        $this->DI = $DI;
        $this->adminEmail = $adminEmail;
        $this->appName = $appName;
        $this->emailUserRegistrationConfirmationExpire = $emailUserRegistrationConfirmationExpire;
    }

    public function __invoke(SendEmailRegistrationConfirmInputDto $emailInfo): void
    {
        $email = $this->createEmailDto(
            $emailInfo->id,
            $emailInfo->emailTo,
            $this->appName,
            $this->emailUserRegistrationConfirmationExpire
        );

        $this->mailer
            ->subject($email->subject)
            ->from($email->from)
            ->to($email->to)
            ->template($email->dto);

        $this->mailer->send();
    }

    private function createEmailDto(string $id, string $emailTo, string $appName, int $emailUserRegistrationConfirmationExpire): EmailDto
    {
        return new EmailDto(
            $this->translator->translate('subject', ['appName' => $appName], EmailRegistrationConfirmationDto::TRANSLATOR_DOMAIN),
            $this->adminEmail,
            $emailTo,
            $this->createEmailTemplateData($id, $appName, $emailUserRegistrationConfirmationExpire)
        );
    }

    private function createEmailTemplateData(string $id, string $appName, int $emailUserRegistrationConfirmationExpire): EmailRegistrationConfirmationDto
    {
        return (new EmailRegistrationConfirmationDto($this->translator))(
            $appName,
            TemplateId::create('title'),
            TemplateId::create('welcome', ['appName' => $appName]),
            $this->getUrlRegistrationConfirmation($id, $emailUserRegistrationConfirmationExpire),
            TemplateId::create('urlRegistrationConfirmationText'),
            TemplateId::create('farewell'),
        );
    }

    private function getUrlRegistrationConfirmation(string $id, int $emailUserRegistrationConfirmationExpire): string
    {
        $token = $this->jwt->encode(['id' => $id], $emailUserRegistrationConfirmationExpire);

        return $this->DI->getUrlRouteAbsolute('user_email_confirmation', ['token' => $token]);
    }
}
