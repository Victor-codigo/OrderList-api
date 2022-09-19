<?php

declare(strict_types=1);

namespace User\Domain\Service;

class UserRegisterKeyValidationService
{
    private readonly string $registerKey;

    public function __construct(string $registerKey)
    {
        $this->registerKey = $registerKey;
    }

    public function __invoke(string $key): bool
    {
        return $this->registerKey === $key ? true : false;
    }
}
