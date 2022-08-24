<?php

declare(strict_types=1);

namespace User\Dao;

final class UserCreateDao
{
    public readonly string $email;
    public readonly string $password;
    public readonly string $name;

    public function __construct(string $email, string $password, string $name)
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
    }
}
