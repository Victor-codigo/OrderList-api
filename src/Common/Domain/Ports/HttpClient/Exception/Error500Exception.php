<?php

declare(strict_types=1);

namespace Common\Domain\HttpClient\Exception;

use Common\Adapter\HttpClient\HttpClientResponse;
use Common\Domain\Ports\HttpCllent\HttpClientResponseInteface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class Error500Exception extends \DomainException
{
    public static function fromMessage(string $message, HttpExceptionInterface $exception): static
    {
        return new static($message, 0, $exception);
    }

    public function getResponse(): HttpClientResponseInteface
    {
        /** @var HttpExceptionInterface $exception */
        $exception = $this->getPrevious();

        return new HttpClientResponse($exception->getResponse());
    }
}
