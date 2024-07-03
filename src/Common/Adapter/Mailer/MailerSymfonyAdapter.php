<?php

declare(strict_types=1);

namespace Common\Adapter\Mailer;

use Common\Domain\Mailer\Exception\MailerSentException;
use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;
use Common\Domain\Ports\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailInterface;

class MailerSymfonyAdapter implements MailerInterface
{
    private SymfonyMailInterface $mailer;
    private TemplatedEmail $email;

    public function __construct(SymfonyMailInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->email = $this->createEmail();
    }

    /**
     * @throws MailerSentException
     */
    #[\Override]
    public function send(): void
    {
        try {
            $this->mailer->send($this->email);
        } catch (TransportExceptionInterface $e) {
            throw MailerSentException::fromMessage($e->getMessage());
        }
    }

    #[\Override]
    public function from(string $from): self
    {
        $this->email->from($from);

        return $this;
    }

    #[\Override]
    public function to(string $to): self
    {
        $this->email->to($to);

        return $this;
    }

    #[\Override]
    public function subject(string $subject): self
    {
        $this->email->subject($subject);

        return $this;
    }

    #[\Override]
    public function text(string $text): self
    {
        $this->email->text($text);

        return $this;
    }

    #[\Override]
    public function html(string $html): self
    {
        $this->email->html($html);

        return $this;
    }

    #[\Override]
    public function template(TemplateDtoInterface $data): self
    {
        $this->email
            ->htmlTemplate($data->getPath())
            ->context($data->toArray());

        return $this;
    }

    #[\Override]
    public function cc(string ...$text): self
    {
        $this->email->cc(...$text);

        return $this;
    }

    #[\Override]
    public function attachFile(string $filePath, string $fileName, string $mimeType): self
    {
        $this->email->attachFromPath($filePath, $fileName, $mimeType);

        return $this;
    }

    private function createEmail(): TemplatedEmail
    {
        return new TemplatedEmail();
    }
}
