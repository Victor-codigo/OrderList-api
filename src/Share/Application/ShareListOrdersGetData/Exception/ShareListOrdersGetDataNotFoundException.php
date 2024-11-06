<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ShareListOrdersGetDataNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['list_orders_shared_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
