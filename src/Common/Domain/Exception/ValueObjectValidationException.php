<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use Common\Domain\Validation\VALIDATION_ERRORS;

class ValueObjectValidationException extends DomainException
{
    private function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function create(string $message): self
    {
        return new self($message);
    }

    /**
     * @param VALIDATION_ERRORS[] $errors
     */
    public static function createFromArray(array $errors): self
    {
        $errorsNames = array_map(fn (VALIDATION_ERRORS $error) => $error->name, $errors);

        return self::create(sprintf(
            'ValueObject validation errors [%s]',
            implode(', ', $errorsNames)
        ));
    }
}
