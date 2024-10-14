<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;

class ValueObjectValidationException extends DomainExceptionOutput implements ValidationExceptionInterface
{
    /**
     * @param array<int|string, array{}|array<int, VALIDATION_ERRORS|VALIDATION_ERRORS[]>> $errors
     */
    public static function fromArray(string $message, array $errors): self
    {
        $errorValueObject = [];

        foreach ($errors as $valueObjectName => $errorList) {
            $errorValueObject[$valueObjectName] = self::errorsToLower($errorList);
        }

        return new static($message, $errorValueObject, RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }

    /**
     * @param array{}|array<int, VALIDATION_ERRORS|VALIDATION_ERRORS[]> $errorList
     *
     * @return array{}|array<int, VALIDATION_ERRORS|VALIDATION_ERRORS[]>
     */
    private static function errorsToLower(array $errorList): array
    {
        $errorListToLower = [];
        foreach ($errorList as $key => $error) {
            if (is_array($error)) {
                $errorListToLower[$key] = self::errorsToLower($error);

                continue;
            }

            $errorListToLower[$key] = mb_strtolower($error->name);
        }

        return $errorListToLower;
    }
}
