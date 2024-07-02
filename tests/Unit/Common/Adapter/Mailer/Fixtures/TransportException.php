<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Mailer\Fixtures;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class TransportException extends Exception implements TransportExceptionInterface
{
    #[\Override]
    public function getDebug(): string
    {
        return '';
    }

    #[\Override]
    public function appendDebug(string $debug): void
    {
    }
}
