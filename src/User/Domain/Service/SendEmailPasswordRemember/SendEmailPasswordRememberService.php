<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailPasswordRemember;

use Common\Domain\HtmlTemplate\TemplateId;
use Common\Domain\Mailer\EmailDto;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Ports\Mailer\MailerInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use User\Adapter\Templates\Email\EmailPasswordRemember\EmailPasswordRememberDto;
use User\Domain\Service\SendEmailPasswordRemember\Dto\SendEmailPasswordRememberDto;

class SendEmailPasswordRememberService
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;
    private JwtHS256Interface $jwt;
    private string $adminEmail;
    private string $appName;
    private int $emailUserPasswordRememberExpire;
    private string $passwordRememberUrl;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        JwtHS256Interface $jwt,
        string $adminEmail,
        string $appName,
        int $emailUserPasswordRememberExpire,
        string $passwordRememberUrl
        ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->jwt = $jwt;
        $this->adminEmail = $adminEmail;
        $this->appName = $appName;
        $this->emailUserPasswordRememberExpire = $emailUserPasswordRememberExpire;
        $this->passwordRememberUrl = $passwordRememberUrl;
    }

    public function __invoke(SendEmailPasswordRememberDto $emailInfo): void
    {
        $email = $this->createEmailDto(
            $emailInfo->id,
            $emailInfo->emailTo,
            $emailInfo->userName,
            $this->appName,
            $this->emailUserPasswordRememberExpire,
            $this->passwordRememberUrl
        );

        $this->mailer
            ->subject($email->subject)
            ->from($email->from)
            ->to($email->to)
            ->template($email->dto);

        $this->mailer->send();
    }

    private function createEmailDto(Identifier $id, Email $emailTo, Name $userName, string $appName, int $emailUserPasswordRememberExpire, string $passwordRememberUrl): EmailDto
    {
        return new EmailDto(
            $this->translator->translate('subject', ['appName' => $appName], EmailPasswordRememberDto::TRANSLATOR_DOMAIN),
            $this->adminEmail,
            $emailTo->getValue(),
            $this->createEmailTemplateData($id, $userName, $appName, $emailUserPasswordRememberExpire, $passwordRememberUrl)
        );
    }

    private function createEmailTemplateData(Identifier $id, Name $userName, string $appName, int $emailUserPasswordRememberExpire, string $passwordRememberUrl): EmailPasswordRememberDto
    {
        return (new EmailPasswordRememberDto($this->translator))(
            $appName,
            $userName->getValue(),
            TemplateId::create('title'),
            TemplateId::create('welcome', ['userName' => $userName->getValue()]),
            $this->getUrlPaswordRestoration($id, $emailUserPasswordRememberExpire, $passwordRememberUrl),
            TemplateId::create('buttonRestorationText'),
            TemplateId::create('farewell'),
        );
    }

    private function getUrlPaswordRestoration(Identifier $id, int $emailUserPasswordRememberExpire, string $passwordRememberUrl): string
    {
        $token = $this->jwt->encode(['username' => $id->getValue()], $emailUserPasswordRememberExpire);

        return $passwordRememberUrl.'/'.$token;
    }
}
