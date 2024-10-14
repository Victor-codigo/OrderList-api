<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

interface DomainExceptionOutputInterface
{
    public function getMessage(): string;

    /**
     * @return array<string, string>
     */
    public function getErrors(): array;

    public function getStatus(): RESPONSE_STATUS;

    public function getHttpStatus(): RESPONSE_STATUS_HTTP;
}
