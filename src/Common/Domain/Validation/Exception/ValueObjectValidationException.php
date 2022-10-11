<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Exception;

use Common\Domain\Exception\DomainException;
use Common\Domain\Validation\VALIDATION_ERRORS;

class ValueObjectValidationException extends DomainException implements ValidationExceptionInterface
{
    /**
     * @param VALIDATION_ERRORS[] $errors
     */
    public static function fromArray(array $errors): self
    {
        $errorsNames = array_map(fn (VALIDATION_ERRORS $error) => $error->name, $errors);

        return new static(sprintf(
            'ValueObject validation errors [%s]',
            implode(', ', $errorsNames)
        ));
    }
}
