<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;

class DomainExceptionOutput extends DomainException
{
    public RESPONSE_STATUS_HTTP $httpStatus;
    public RESPONSE_STATUS $status;
    /**
     * @var array<string, string>
     */
    private array $errors;

    /**
     * @param array<int|string, array{}|string|string[]|VALIDATION_ERRORS[]|array<int, VALIDATION_ERRORS[]>> $errors
     */
    final protected function __construct(string $message, array $errors, RESPONSE_STATUS $status, RESPONSE_STATUS_HTTP $httpStatus)
    {
        $this->message = $message;
        $this->errors = $errors;
        $this->httpStatus = $httpStatus;
        $this->status = $status;
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getHttpStatus(): RESPONSE_STATUS_HTTP
    {
        return $this->httpStatus;
    }

    public function getStatus(): RESPONSE_STATUS
    {
        return $this->status;
    }
}
