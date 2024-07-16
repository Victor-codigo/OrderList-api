<?php

declare(strict_types=1);

namespace User\Domain\Service\SendEmailPasswordRemember;

use Common\Domain\Mailer\EmailDto;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Ports\Mailer\MailerInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use User\Adapter\Templates\Email\EmailPasswordRemember\EmailPasswordRememberDto;
use User\Domain\Service\SendEmailPasswordRemember\Dto\SendEmailPasswordRememberDto;

class SendEmailPasswordRememberService
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private JwtHS256Interface $jwt,
        private EmailPasswordRememberDto $emailPasswordRememberDto,
        private string $adminEmail,
        private string $appName,
        private int $emailUserPasswordRememberExpire
    ) {
    }

    public function __invoke(SendEmailPasswordRememberDto $emailInfo): void
    {
        $email = $this->createEmailDto(
            $emailInfo->id,
            $emailInfo->emailTo,
            $emailInfo->userName,
            $this->appName,
            $this->emailUserPasswordRememberExpire,
            $emailInfo->passwordRememberUrl
        );

        $this->mailer
            ->subject($email->subject)
            ->from($email->from)
            ->to($email->to)
            ->template($email->dto);

        $this->mailer->send();
    }

    private function createEmailDto(Identifier $id, Email $emailTo, NameWithSpaces $userName, string $appName, int $emailUserPasswordRememberExpire, Url $passwordRememberUrl): EmailDto
    {
        return new EmailDto(
            $this->translator->translate('subject', ['appName' => $appName], EmailPasswordRememberDto::TRANSLATOR_DOMAIN),
            $this->adminEmail,
            $emailTo->getValue(),
            $this->createEmailTemplateData($id, $userName, $appName, $emailUserPasswordRememberExpire, $passwordRememberUrl)
        );
    }

    private function createEmailTemplateData(Identifier $id, NameWithSpaces $userName, string $appName, int $emailUserPasswordRememberExpire, Url $passwordRememberUrl): EmailPasswordRememberDto
    {
        return $this->emailPasswordRememberDto->setData(
            $appName,
            $userName->getValue(),
            $this->getUrlPasswordRestoration($id, $emailUserPasswordRememberExpire, $passwordRememberUrl),
            $emailUserPasswordRememberExpire,
        );
    }

    private function getUrlPasswordRestoration(Identifier $id, int $emailUserPasswordRememberExpire, Url $passwordRememberUrl): string
    {
        $token = $this->jwt->encode(['username' => $id->getValue()], $emailUserPasswordRememberExpire);

        return $passwordRememberUrl->getValue().'/'.$token;
    }
}
