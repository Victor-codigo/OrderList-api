<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange\Dto;

class UserPasswordChangeOutputDto
{
    public readonly bool $success;

    public function __construct(bool $success)
    {
        $this->success = $success;
    }
}
