<?php

namespace Common\Domain\Mailer;

use DomainException;

class MailerSentException extends DomainException
{
    private function __construct(string $message, int $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public static function create(string $message, int $code = 0): self
    {
        return new self($message, $code);
    }
}
