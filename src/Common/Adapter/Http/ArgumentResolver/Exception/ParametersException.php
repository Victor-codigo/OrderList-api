<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ParametersException extends BadRequestHttpException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
