<?php

declare(strict_types=1);

namespace User\Domain\Service\UserRegisterKeyValidation;

use User\Domain\Service\UserRegisterKeyValidation\Dto\UserRegisterKeyValidationInputDto;

class UserRegisterKeyValidationService
{
    private readonly string $registerKey;

    public function __construct(string $registerKey)
    {
        $this->registerKey = $registerKey;
    }

    public function __invoke(UserRegisterKeyValidationInputDto $registerValidation): bool
    {
        return $this->registerKey === $registerValidation->key ? true : false;
    }
}
