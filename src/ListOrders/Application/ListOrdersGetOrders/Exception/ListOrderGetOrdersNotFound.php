<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetOrders\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ListOrderGetOrdersNotFound extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['orders_not_found' => $message], RESPONSE_STATUS::OK, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
