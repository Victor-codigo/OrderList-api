<?php

declare(strict_types=1);

namespace User\Domain\Service\UserRegisterKeyValidation\Dto;

class UserRegisterKeyValidationInputDto
{
    public readonly string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }
}
