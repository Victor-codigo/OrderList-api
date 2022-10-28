<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRemember\Dto;

class UserPasswordRememberOutputDto
{
    public readonly bool $success;

    public function __construct(bool $success)
    {
        $this->success = $success;
    }
}
