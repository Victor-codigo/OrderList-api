<?php

declare(strict_types=1);

namespace Product\Application\ProductGetFirstLetter\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ProductGetFirstLetterProductsNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['product_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::NO_CONTENT);
    }
}
