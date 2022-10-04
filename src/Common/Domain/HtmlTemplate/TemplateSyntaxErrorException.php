<?php

namespace Common\Domain\HtmlTemplate;

use Error;

class TemplateSyntaxErrorException extends Error
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
