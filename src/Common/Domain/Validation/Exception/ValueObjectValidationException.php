<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Validation\VALIDATION_ERRORS;

class ValueObjectValidationException extends DomainExceptionOutput implements ValidationExceptionInterface
{
    /**
     * @param array<string, VALIDATION_ERRORS[]> $errors
     */
    public static function fromArray(string $message, array $errors): self
    {
        $errorValueObject = [];

        foreach ($errors as $valueObjectName => $errorList) {
            $errorValueObject[$valueObjectName] = array_map(
                fn (VALIDATION_ERRORS $error) => mb_strtolower($error->name),
                $errorList
            );
        }

        return new static($message, $errorValueObject, RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
