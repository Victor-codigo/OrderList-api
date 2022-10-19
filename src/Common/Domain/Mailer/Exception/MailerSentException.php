<?php

namespace Common\Domain\Mailer\Exception;

use Common\Domain\Exception\DomainException;

class MailerSentException extends DomainException implements MailerExceptionInterface
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
