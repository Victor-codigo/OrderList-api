<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailRegisterConfirm;

use Common\Domain\Mailer\EmailDto;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Url;
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
    private EmailRegistrationConfirmationDto $emailRegistrationConfirmationDto;
    private string $adminEmail;
    private string $appName;
    private int $emailUserRegistrationConfirmationExpire;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        JwtHS256Interface $jwt,
        EmailRegistrationConfirmationDto $emailRegistrationConfirmationDto,
        string $adminEmail,
        string $appName,
        int $emailUserRegistrationConfirmationExpire
        ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->jwt = $jwt;
        $this->emailRegistrationConfirmationDto = $emailRegistrationConfirmationDto;
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
            $this->emailUserRegistrationConfirmationExpire,
            $emailInfo->userRegisterEmailConfirmationUrl
        );

        $this->mailer
            ->subject($email->subject)
            ->from($email->from)
            ->to($email->to)
            ->template($email->dto);

        $this->mailer->send();
    }

    private function createEmailDto(Identifier $id, Email $emailTo, string $appName, int $emailUserRegistrationConfirmationExpire, Url $registrationConfirmUrl): EmailDto
    {
        return new EmailDto(
            $this->translator->translate('subject', ['appName' => $appName], EmailRegistrationConfirmationDto::TRANSLATOR_DOMAIN),
            $this->adminEmail,
            $emailTo->getValue(),
            $this->createEmailTemplateData($id, $appName, $emailUserRegistrationConfirmationExpire, $registrationConfirmUrl)
        );
    }

    private function createEmailTemplateData(Identifier $id, string $appName, int $emailUserRegistrationConfirmationExpire, Url $registrationConfirmUrl): EmailRegistrationConfirmationDto
    {
        return $this->emailRegistrationConfirmationDto->setData(
            $appName,
            $this->getUrlRegistrationConfirmation($id, $emailUserRegistrationConfirmationExpire, $registrationConfirmUrl),
            $emailUserRegistrationConfirmationExpire
        );
    }

    private function getUrlRegistrationConfirmation(Identifier $id, int $emailUserRegistrationConfirmationExpire, Url $registrationConfirmUrl): string
    {
        $token = $this->jwt->encode(['username' => $id->getValue()], $emailUserRegistrationConfirmationExpire);

        return $registrationConfirmUrl->getValue().'/'.$token;
    }
}
