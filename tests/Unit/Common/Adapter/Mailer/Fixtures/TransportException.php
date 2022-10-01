<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Mailer\Fixtures;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class TransportException extends Exception implements TransportExceptionInterface
{
    public function getDebug(): string
    {
        return '';
    }

    public function appendDebug(string $debug): void
    {
    }
}
