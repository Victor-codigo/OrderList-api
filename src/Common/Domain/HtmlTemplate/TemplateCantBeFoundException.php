<?php

namespace Common\Domain\Exception;

use Error;

class TemplateCantBeFoundException extends Error
{
    private function __construct(string $message, int $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public static function create(string $message, int $code): self
    {
        return new self($message, $code);
    }
}
