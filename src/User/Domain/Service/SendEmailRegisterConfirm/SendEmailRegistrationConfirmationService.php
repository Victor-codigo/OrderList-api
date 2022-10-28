<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm;

use Common\Domain\HtmlTemplate\TemplateId;
use Common\Domain\Mailer\EmailDto;
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
    private string $adminEmail;
    private string $appName;
    private int $emailUserRegistrationConfirmationExpire;
    private string $registrationConfirmUrl;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        JwtHS256Interface $jwt,
        string $adminEmail,
        string $appName,
        int $emailUserRegistrationConfirmationExpire,
        string $registrationConfirmUrl
        ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->jwt = $jwt;
        $this->adminEmail = $adminEmail;
        $this->appName = $appName;
        $this->emailUserRegistrationConfirmationExpire = $emailUserRegistrationConfirmationExpire;
        $this->registrationConfirmUrl = $registrationConfirmUrl;
    }

    public function __invoke(SendEmailRegistrationConfirmInputDto $emailInfo): void
    {
        $email = $this->createEmailDto(
            $emailInfo->id,
            $emailInfo->emailTo,
            $this->appName,
            $this->emailUserRegistrationConfirmationExpire,
            $this->registrationConfirmUrl
        );

        $this->mailer
            ->subject($email->subject)
            ->from($email->from)
            ->to($email->to)
            ->template($email->dto);

        $this->mailer->send();
    }

    private function createEmailDto(string $id, string $emailTo, string $appName, int $emailUserRegistrationConfirmationExpire, string $registrationConfirmUrl): EmailDto
    {
        return new EmailDto(
            $this->translator->translate('subject', ['appName' => $appName], EmailRegistrationConfirmationDto::TRANSLATOR_DOMAIN),
            $this->adminEmail,
            $emailTo,
            $this->createEmailTemplateData($id, $appName, $emailUserRegistrationConfirmationExpire, $registrationConfirmUrl)
        );
    }

    private function createEmailTemplateData(string $id, string $appName, int $emailUserRegistrationConfirmationExpire, string $registrationConfirmUrl): EmailRegistrationConfirmationDto
    {
        return (new EmailRegistrationConfirmationDto($this->translator))(
            $appName,
            TemplateId::create('title'),
            TemplateId::create('welcome', ['appName' => $appName]),
            $this->getUrlRegistrationConfirmation($id, $emailUserRegistrationConfirmationExpire, $registrationConfirmUrl),
            TemplateId::create('urlRegistrationConfirmationText'),
            TemplateId::create('farewell'),
        );
    }

    private function getUrlRegistrationConfirmation(string $id, int $emailUserRegistrationConfirmationExpire, string $registrationConfirmUrl): string
    {
        $token = $this->jwt->encode(['id' => $id], $emailUserRegistrationConfirmationExpire);

        return $registrationConfirmUrl.'/'.$token;
    }
}
