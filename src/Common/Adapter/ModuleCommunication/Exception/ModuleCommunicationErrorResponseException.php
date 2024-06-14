<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleCommunication\Exception;

use Common\Domain\Exception\DomainException;

class ModuleCommunicationErrorResponseException extends DomainException
{
    private array $responseErrors;

    public function __construct(string $message, array $responseErrors)
    {
        parent::__construct($message);

        $this->responseErrors = $responseErrors;
    }

    public static function fromResponseError(array $responseErrors): static
    {
        return new static('Response errors',$responseErrors);
    }

    public function getResponseErrors(): array
    {
        return $this->responseErrors;
    }
}
