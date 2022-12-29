<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class InvalidJsonException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['invalid_json'], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
