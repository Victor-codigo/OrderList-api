<?php

declare(strict_types=1);

namespace Product\Application\ProductCreate\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ProductCreateCanNotUploadFileException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['product_file_upload_error' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
