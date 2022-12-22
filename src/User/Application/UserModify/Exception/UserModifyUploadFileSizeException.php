<?php

declare(strict_types=1);

namespace User\Application\UserModify\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserModifyUploadFileSizeException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['user_file_size' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
