<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use Common\Domain\Response\RESPONSE_STATUS;

interface DomainExceptionOutputInterface
{
    public function getMessage(): string;

    public function getErrors(): array;

    public function getStatus(): RESPONSE_STATUS;

    public function getHttpStatus(): int;
}
