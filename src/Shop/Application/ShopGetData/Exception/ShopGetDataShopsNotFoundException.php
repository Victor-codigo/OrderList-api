<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ShopGetDataShopsNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['shop_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::NO_CONTENT);
    }
}
